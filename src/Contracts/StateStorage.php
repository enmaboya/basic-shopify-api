<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI\Contracts;

use Gnikyt\BasicShopifyAPI\Session;

/**
 * Reprecents basic state storage.
 * Based on spatie/guzzle-rate-limiter-middleware.
 */
interface StateStorage
{
    /**
     * Get all container values.
     */
    public function all(): array;

    /**
     * Get the values.
     *
     * @param Session $session the shop session
     */
    public function get(Session $session): array;

    /**
     * Set the values.
     *
     * @param array   $values  the values to set
     * @param Session $session the shop session
     */
    public function set(array $values, Session $session): void;

    /**
     * Set the values.
     *
     * @param mixed   $value   the value to add
     * @param Session $session the shop session
     */
    public function push($value, Session $session): void;

    /**
     * Remove all values.
     *
     * @param Session $session the shop session
     */
    public function reset(Session $session): void;
}
