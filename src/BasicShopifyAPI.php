<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI;

use Closure;
use Gnikyt\BasicShopifyAPI\Clients\{Graph, Rest};
use Gnikyt\BasicShopifyAPI\Contracts\{ClientAware, GraphRequester, RestRequester, SessionAware, StateStorage, TimeDeferrer};
use Gnikyt\BasicShopifyAPI\Deferrers\Sleep;
use Gnikyt\BasicShopifyAPI\Middleware\{AuthRequest, RateLimiting, UpdateApiLimits, UpdateRequestTime};
use Gnikyt\BasicShopifyAPI\Store\Memory;
use Gnikyt\BasicShopifyAPI\Traits\ResponseTransform;
use GuzzleHttp\{Client, ClientInterface, HandlerStack};
use GuzzleHttp\Promise\Promise;
use GuzzleRetry\GuzzleRetryMiddleware;

/**
 * Basic Shopify API for REST & GraphQL.
 */
class BasicShopifyAPI implements SessionAware, ClientAware
{
    use ResponseTransform;

    /**
     * Header for per-shop API call limits (recieve).
     *
     * @var string
     */
    public const HEADER_REST_API_LIMITS = 'http_x_shopify_shop_api_call_limit';

    /**
     * Header for access token (send).
     *
     * @var string
     */
    public const HEADER_ACCESS_TOKEN = 'x-shopify-access-token';

    /**
     * The Guzzle client.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * The handler stack.
     *
     * @var HandlerStack
     */
    protected $stack;

    /**
     * The GraphQL client.
     *
     * @var GraphRequester
     */
    protected $graphClient;

    /**
     * The REST client.
     *
     * @var RestRequester
     */
    protected $restClient;

    /**
     * The library options.
     *
     * @var Options
     */
    protected $options;

    /**
     * The API session.
     *
     * @var Session|null
     */
    protected $session;

    /**
     * Request timestamp for every new call.
     * Used for rate limiting.
     *
     * @var int
     */
    protected $requestTimestamp;

    /**
     * Constructor.
     *
     * @param Options           $options   the options for the library setup
     * @param StateStorage|null $tstore    the time storer implementation to use for rate limiting
     * @param StateStorage|null $lstore    the limits storer implementation to use for rate limiting
     * @param TimeDeferrer|null $tdeferrer the time deferrer implementation to use for rate limiting
     *
     * @return self
     */
    public function __construct(
        Options $options,
        ?StateStorage $tstore = null,
        ?StateStorage $lstore = null,
        ?TimeDeferrer $tdeferrer = null
    ) {
        // Setup REST and GraphQL clients
        $this->setupClients($tstore, $lstore, $tdeferrer);

        // Set the options
        $this->setOptions($options);

        // Create the stack and assign the middleware which attempts to fix redirects
        $this->stack = HandlerStack::create($this->getOptions()->getGuzzleHandler());
        $this
            ->addMiddleware(new AuthRequest($this), 'request:auth')
            ->addMiddleware(new UpdateApiLimits($this), 'rate:update')
            ->addMiddleware(new UpdateRequestTime($this), 'time:update')
            ->addMiddleware(GuzzleRetryMiddleware::factory(), 'request:retry');
        if ($this->getOptions()->isRateLimitingEnabled()) {
            $this->addMiddleware(new RateLimiting($this), 'rate:limiting');
        }

        // Create a default Guzzle client with our stack
        $this->setClient(
            new Client(array_merge(
                ['handler' => $this->stack],
                $this->getOptions()->getGuzzleOptions()
            ))
        );
    }

    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
        $this->getGraphClient()->setClient($this->client);
        $this->getRestClient()->setClient($this->client);
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function setOptions(Options $options): void
    {
        $this->options = $options;
        $this->getGraphClient()->setOptions($this->options);
        $this->getRestClient()->setOptions($this->options);
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    /**
     * Sets the GraphQL request client.
     *
     * @param GraphRequester $client the client for GraphQL
     */
    public function setGraphClient(GraphRequester $client): self
    {
        $this->graphClient = $client;

        return $this;
    }

    /**
     * Get the GraphQL client.
     */
    public function getGraphClient(): GraphRequester
    {
        return $this->graphClient;
    }

    /**
     * Sets the REST request client.
     *
     * @param RestRequester $client the client for REST
     */
    public function setRestClient(RestRequester $client): self
    {
        $this->restClient = $client;

        return $this;
    }

    /**
     * Get the REST client.
     */
    public function getRestClient(): RestRequester
    {
        return $this->restClient;
    }

    public function setSession(Session $session): void
    {
        $this->session = $session;
        $this->getGraphClient()->setSession($this->session);
        $this->getRestClient()->setSession($this->session);
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    /**
     * Accepts a closure to do isolated API calls for a shop.
     *
     * @param Session $session the shop/user session
     *
     * @throws \Exception when closure is missing or not callable
     */
    public function withSession(Session $session, \Closure $closure)
    {
        // Clone the API class and bind it to the closure
        $clonedApi = clone $this;
        $clonedApi->setSession($session);

        return $closure->call($clonedApi);
    }

    /**
     * Add middleware to the handler stack.
     *
     * @param callable $callable middleware function
     * @param string   $name     name to register for this middleware
     */
    public function addMiddleware(callable $callable, string $name = ''): self
    {
        $this->stack->push($callable, $name);

        return $this;
    }

    /**
     * Remove middleware to the handler stack.
     *
     * @param string $name name to register for this middleware
     */
    public function removeMiddleware(string $name = ''): self
    {
        $this->stack->remove($name);

        return $this;
    }

    /**
     * @see Rest::getAuthUrl
     */
    public function getAuthUrl($scopes, string $redirectUri, string $mode = 'offline'): string
    {
        return $this->getRestClient()->getAuthUrl($scopes, $redirectUri, $mode);
    }

    /**
     * @see Rest::requestAccess
     */
    public function requestAccess(string $code): ResponseAccess
    {
        return $this->getRestClient()->requestAccess($code);
    }

    /**
     * Gets the access token from a "code" supplied by Shopify request after successfull auth (for public apps).
     *
     * @param string $code the code from Shopify
     */
    public function requestAccessToken(string $code): string
    {
        return $this->requestAccess($code)['access_token'];
    }

    /**
     * Gets the access object from a "code" and sets it to the instance (for public apps).
     *
     * @param string $code the code from Shopify
     */
    public function requestAndSetAccess(string $code): void
    {
        // Get the access response data
        $access = $this->requestAccess($code);

        // Setup the additional user data (if available)
        $user = [];
        if (isset($access['associated_user'])) {
            $keys = ['associated_user', 'associated_user_scope', 'expires_in', 'session', 'account_number'];
            foreach ($keys as $key) {
                $user[$key] = $access[$key] ?? null;
            }
        }

        $session = new Session(
            $this->session->getShop(),
            $access['access_token'],
            new ResponseAccess($user)
        );

        // Update the session
        $this->setSession($session);
    }

    /**
     * Verify the request is from Shopify using the HMAC signature (for public apps).
     *
     * @param array $params The request parameters (ex. $_GET).
     *
     * @return bool if the HMAC is validated
     * @throws \Exception for missing API secret
     */
    public function verifyRequest(array $params): bool
    {
        if ($this->getOptions()->getApiSecret() === null) {
            // Secret is required
            throw new \Exception('API secret is missing');
        }

        // Ensure shop, timestamp, and HMAC are in the params
        if (array_key_exists('shop', $params)
            && array_key_exists('timestamp', $params)
            && array_key_exists('hmac', $params)
        ) {
            // Grab the HMAC, remove it from the params, then sort the params for hashing
            $hmac = $params['hmac'];
            unset($params['hmac']);

            // Convert array values in the params to a string
            foreach ($params as &$value) {
                if (is_array($value)) {
                    $value = '["' . implode('", "', $value) . '"]';
                }
            }

            ksort($params);

            // Encode and hash the params (without HMAC), add the API secret, and compare to the HMAC from params
            return $hmac === hash_hmac(
                'sha256',
                urldecode(http_build_query($params)),
                $this->options->getApiSecret()
            );
        }

        // Not valid
        return false;
    }

    /**
     * Alias for REST method for backwards compatibility.
     *
     * @see Rest
     */
    public function request()
    {
        return call_user_func_array(
            [$this, 'rest'],
            func_get_args()
        );
    }

    /**
     * @see Graph::request
     */
    public function graph(string $query, array $variables = [], bool $sync = true)
    {
        return $this->getGraphClient()->request($query, $variables, $sync);
    }

    /**
     * Runs a request to the Shopify API (async).
     *
     * @see Graph
     */
    public function graphAsync(string $query, array $variables = []): Promise
    {
        return $this->graph($query, $variables, false);
    }

    /**
     * @see Rest::request
     */
    public function rest(string $type, string $path, ?array $params = null, array $headers = [], bool $sync = true)
    {
        return $this->getRestClient()->request($type, $path, $params, $headers, $sync);
    }

    /**
     * Runs a request to the Shopify API (async).
     * Alias for `rest` with `sync` param set to `false`.
     *
     * @see Rest
     */
    public function restAsync(string $type, string $path, ?array $params = null, array $headers = []): Promise
    {
        return $this->rest($type, $path, $params, $headers, false);
    }

    /**
     * Setup the REST and GraphQL clients.
     *
     * @param StateStorage|null $tstore    the time storer implementation to use for rate limiting
     * @param StateStorage|null $lstore    the limits storer implementation to use for rate limiting
     * @param TimeDeferrer|null $tdeferrer the time deferrer implementation to use for rate limiting
     */
    protected function setupClients(
        ?StateStorage $tstore = null,
        ?StateStorage $lstore = null,
        ?TimeDeferrer $tdeferrer = null
    ): void {
        // Base/default storage class if none provided
        $baseStorage = Memory::class;

        // Setup timestamp storage
        $graphTstore = $tstore === null ? new $baseStorage() : clone $tstore;
        $restTstore = $tstore === null ? new $baseStorage() : clone $tstore;

        // Setup limits storage
        $graphLstore = $lstore === null ? new $baseStorage() : clone $lstore;
        $restLstore = $lstore === null ? new $baseStorage() : clone $lstore;

        // Setup time deferrer
        $tdeferrer = $tdeferrer ?? new Sleep();

        // Setup REST and Graph clients
        $this->setRestClient(new Rest($restTstore, $restLstore, $tdeferrer));
        $this->setGraphClient(new Graph($graphTstore, $graphLstore, $tdeferrer));
    }
}
