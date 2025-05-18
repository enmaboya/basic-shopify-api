<?php

namespace Gnikyt\BasicShopifyAPI\Contracts;

/**
 * Reprecents common request limits tracking.
 */
interface LimitAccesser
{
    /**
     * Get the limit store implementation.
     */
    public function getLimitStore(): StateStorage;
}
