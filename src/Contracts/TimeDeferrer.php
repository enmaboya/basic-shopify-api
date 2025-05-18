<?php

namespace Gnikyt\BasicShopifyAPI\Contracts;

/**
 * Reprecents basic time handling for getting and sleeping.
 * Based on spatie/guzzle-rate-limiter-middleware.
 */
interface TimeDeferrer
{
    /**
     * Get the current timestamp with microseconds.
     */
    public function getCurrentTime(): float;

    /**
     * Sleep for a number of microseconds.
     */
    public function sleep(float $microseconds): void;
}
