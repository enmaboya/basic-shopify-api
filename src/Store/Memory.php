<?php

namespace Gnikyt\BasicShopifyAPI\Store;

use Gnikyt\BasicShopifyAPI\Contracts\StateStorage;
use Gnikyt\BasicShopifyAPI\Session;

/**
 * In-memory storage for timestamps used by rate limit middleware.
 * Based on spatie/guzzle-rate-limiter-middleware.
 */
class Memory implements StateStorage
{
    /**
     * The data container.
     *
     * @var array
     */
    protected $container = [];

    public function all(): array
    {
        return $this->container;
    }

    public function get(Session $session): array
    {
        $shop = $session->getShop();

        return $this->container[$shop] ?? [];
    }

    public function set(array $values, Session $session): void
    {
        $this->container[$session->getShop()] = $values;
    }

    public function push($value, Session $session): void
    {
        $shop = $session->getShop();
        if (!isset($this->container[$shop])) {
            $this->reset($session);
        }

        array_unshift($this->container[$shop], $value);
    }

    public function reset(Session $session): void
    {
        $this->container[$session->getShop()] = [];
    }
}
