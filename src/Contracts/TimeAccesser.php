<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI\Contracts;

/**
 * Reprecents time tracking.
 */
interface TimeAccesser
{
    /**
     * Get the time store implementation.
     */
    public function getTimeStore(): StateStorage;

    /**
     * Get the time deferrer implementation.
     */
    public function getTimeDeferrer(): TimeDeferrer;
}
