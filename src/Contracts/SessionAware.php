<?php

namespace Gnikyt\BasicShopifyAPI\Contracts;

use Gnikyt\BasicShopifyAPI\Session;

/**
 * Reprecents session awareness.
 */
interface SessionAware
{
    /**
     * Set the session for the API calls.
     *
     * @param Session $session the shop/user session
     */
    public function setSession(Session $session): void;

    /**
     * Get the session.
     */
    public function getSession(): ?Session;
}
