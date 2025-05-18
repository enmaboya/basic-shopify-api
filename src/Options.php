<?php

namespace Gnikyt\BasicShopifyAPI;

/**
 * Options for the library.
 */
class Options
{
    /**
     * API version pattern.
     *
     * @var string
     */
    public const VERSION_PATTERN = '/([0-9]{4}-[0-9]{2})|unstable/';

    /**
     * Private or public API calls.
     *
     * @var bool
     */
    protected $private = false;

    /**
     * The Shopify API key.
     *
     * @var string|null
     */
    protected $apiKey;

    /**
     * The Shopify API password.
     *
     * @var string|null
     */
    protected $apiPassword;

    /**
     * The Shopify API secret.
     *
     * @var string|null
     */
    protected $apiSecret;

    /**
     * How many requests allowed per second.
     *
     * @var int
     */
    protected $restLimit = 2;

    /**
     * How many points allowed to use per second.
     *
     * @var int
     */
    protected $graphLimit = 50;

    /**
     * API version.
     *
     * @var string|null
     */
    protected $version;

    /**
     * Enable or disable built-in rate limiting.
     *
     * @var bool
     */
    protected $rateLimiting = true;

    /**
     * Additional Guzzle options.
     *
     * @var array
     */
    protected $guzzleOptions = [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'timeout' => 10.0,
        'max_retry_attempts' => 2,
        'default_retry_multiplier' => 2.0,
        'retry_on_status' => [429, 503, 500],
    ];

    /**
     * Optional Guzzle handler to use.
     *
     * @var callable|null
     */
    protected $guzzleHandler;

    /**
     * Set type for API calls.
     *
     * @param bool $private true for private, false for public
     */
    public function setType(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get the type for API calls.
     */
    public function getType(): bool
    {
        return $this->private;
    }

    /**
     * Determines if the calls are private.
     */
    public function isPrivate(): bool
    {
        return $this->private === true;
    }

    /**
     * Determines if the calls are public.
     */
    public function isPublic(): bool
    {
        return !$this->isPrivate();
    }

    /**
     * Sets the API key for use with the Shopify API (public or private apps).
     *
     * @param string $apiKey the API key
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get the API key.
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Sets the API secret for use with the Shopify API (public apps).
     *
     * @param string $apiSecret the API secret key
     */
    public function setApiSecret(string $apiSecret): self
    {
        $this->apiSecret = $apiSecret;

        return $this;
    }

    /**
     * Get the API secret.
     */
    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }

    /**
     * Sets the API password for use with the Shopify API (private apps).
     *
     * @param string $apiPassword the API password
     */
    public function setApiPassword(string $apiPassword): self
    {
        $this->apiPassword = $apiPassword;

        return $this;
    }

    /**
     * Get API password.
     */
    public function getApiPassword(): ?string
    {
        return $this->apiPassword;
    }

    /**
     * Set the REST limit.
     */
    public function setRestLimit(int $limit): self
    {
        $this->restLimit = $limit;

        return $this;
    }

    /**
     * Get the REST limit.
     */
    public function getRestLimit(): int
    {
        return $this->restLimit;
    }

    /**
     * Set the GraphQL limit.
     */
    public function setGraphLimit(int $limit): self
    {
        $this->graphLimit = $limit;

        return $this;
    }

    /**
     * Get the GraphQL limit.
     */
    public function getGraphLimit(): int
    {
        return $this->graphLimit;
    }

    /**
     * Set options for Guzzle.
     */
    public function setGuzzleOptions(array $options): self
    {
        $this->guzzleOptions = array_merge($this->guzzleOptions, $options);

        return $this;
    }

    /**
     * Get options for Guzzle.
     */
    public function getGuzzleOptions(): array
    {
        return $this->guzzleOptions;
    }

    /**
     * Set a Guzzle handler.
     */
    public function setGuzzleHandler(callable $handler): self
    {
        $this->guzzleHandler = $handler;

        return $this;
    }

    /**
     * Get the Guzzle handler.
     */
    public function getGuzzleHandler(): ?callable
    {
        return $this->guzzleHandler;
    }

    /**
     * Sets the version of Shopify API to use.
     *
     * @param string $version the API version
     *
     * @throws \Exception if version does not match
     */
    public function setVersion(string $version): self
    {
        if (!preg_match(self::VERSION_PATTERN, $version)) {
            // Invalid version string
            throw new \Exception('Version string must be of YYYY-MM or unstable');
        }

        $this->version = $version;

        return $this;
    }

    /**
     * Returns the current in-use API version.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Enable built-in rate limiting.
     */
    public function enableRateLimiting(): self
    {
        $this->rateLimiting = true;

        return $this;
    }

    /**
     * Disable built-in rate limiting.
     */
    public function disableRateLimiting(): self
    {
        $this->rateLimiting = false;

        return $this;
    }

    /**
     * Is built-in rate limiting enabled?
     */
    public function isRateLimitingEnabled(): bool
    {
        return $this->rateLimiting;
    }
}
