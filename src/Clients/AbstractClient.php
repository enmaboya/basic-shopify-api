<?php

namespace Gnikyt\BasicShopifyAPI\Clients;

use Gnikyt\BasicShopifyAPI\Contracts\{ClientAware, LimitAccesser, Respondable, SessionAware, StateStorage, TimeAccesser, TimeDeferrer};
use Gnikyt\BasicShopifyAPI\{Options, Session};
use Gnikyt\BasicShopifyAPI\Traits\ResponseTransform;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Uri;

/**
 * Base client class.
 */
abstract class AbstractClient implements TimeAccesser, SessionAware, LimitAccesser, ClientAware, Respondable
{
    use ResponseTransform;

    /**
     * The time store implementation.
     *
     * @var StateStorage
     */
    protected $tstore;

    /**
     * The limits store implementation.
     *
     * @var StateStorage
     */
    protected $lstore;

    /**
     * The time deferrer implementation.
     *
     * @var TimeDeferrer
     */
    protected $tdeferrer;

    /**
     * The API session.
     *
     * @var Session|null
     */
    protected $session;

    /**
     * The Guzzle client.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * The options.
     *
     * @var Options
     */
    protected $options;

    /**
     * Setup.
     *
     * @param StateStorage $tstore    the time store implementation
     * @param StateStorage $lstore    the limits store implementation
     * @param TimeDeferrer $tdeferrer the time deferrer implementation
     *
     * @return self
     */
    public function __construct(StateStorage $tstore, StateStorage $lstore, TimeDeferrer $tdeferrer)
    {
        $this->tstore = $tstore;
        $this->lstore = $lstore;
        $this->tdeferrer = $tdeferrer;
    }

    public function getBaseUri(): Uri
    {
        if ($this->session === null || $this->session->getShop() === null) {
            // Shop is required
            throw new \Exception('Shopify domain missing for API calls');
        }

        return new Uri("https://{$this->session->getShop()}");
    }

    public function getTimeDeferrer(): TimeDeferrer
    {
        return $this->tdeferrer;
    }

    public function getTimeStore(): StateStorage
    {
        return $this->tstore;
    }

    public function getLimitStore(): StateStorage
    {
        return $this->lstore;
    }

    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setClient(ClientInterface $client): void
    {
        $this->client = $client;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function setOptions(Options $options): void
    {
        $this->options = $options;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }
}
