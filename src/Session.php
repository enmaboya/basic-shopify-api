<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI;

/**
 * Shop or user session.
 */
class Session
{
    /**
     * The Shopify domain.
     *
     * @var string|null
     */
    protected $shop;

    /**
     * The Shopify access token.
     *
     * @var string|null
     */
    protected $accessToken;

    /**
     * If the API was called with per-user grant option, this will be filled.
     *
     * @var ResponseAccess|null
     */
    protected $user;

    /**
     * Setup a session.
     *
     * @param string              $shop        the shop domain
     * @param string|null         $accessToken the access token for the shop
     * @param ResponseAccess|null $user        the user for per-user
     *
     * @return self
     */
    public function __construct(string $shop, ?string $accessToken = null, ?ResponseAccess $user = null)
    {
        $this->shop = $shop;
        $this->accessToken = $accessToken;
        $this->user = $user instanceof ResponseAccess && count($user->keys()) > 0 ? $user : null;
    }

    /**
     * Gets the access token.
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Gets the Shopify domain (*.myshopify.com) we're working with.
     */
    public function getShop(): ?string
    {
        return $this->shop;
    }

    /**
     * Gets the user.
     */
    public function getUser(): ?ResponseAccess
    {
        return $this->user;
    }

    /**
     * Checks if we have a user.
     */
    public function hasUser(): bool
    {
        return $this->user !== null;
    }
}
