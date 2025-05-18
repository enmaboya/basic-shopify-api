<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI\Contracts;

use Gnikyt\BasicShopifyAPI\ResponseAccess;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Uri;

/**
 * Reprecents REST client.
 */
interface RestRequester extends LimitAccesser, TimeAccesser, SessionAware, ClientAware
{
    /**
     * Runs a request to the Shopify API.
     *
     * @param string     $type    The type of request... GET, POST, PUT, DELETE.
     * @param string     $path    The Shopify API path... /admin/xxxx/xxxx.json.
     * @param array|null $params  optional parameters to send with the request
     * @param array      $headers optional headers to append to the request
     * @param bool       $sync    optionally wait for the request to finish
     *
     * @return array|Promise
     * @throws \Exception
     */
    public function request(string $type, string $path, ?array $params = null, array $headers = [], bool $sync = true);

    /**
     * Gets the access object from a "code" supplied by Shopify request after successfull auth (for public apps).
     *
     * @param string $code the code from Shopify
     *
     * @throws \Exception when API secret is missing
     */
    public function requestAccess(string $code): ResponseAccess;

    /**
     * Returns the base URI to use.
     *
     * @throws \Exception for missing shop domain
     */
    public function getBaseUri(): Uri;

    /**
     * Gets the auth URL for Shopify to allow the user to accept the app (for public apps).
     *
     * @param string|array $scopes      the API scopes as a comma seperated string or array
     * @param string       $redirectUri The valid redirect URI for after acceptance of the permissions.
     *                                  It must match the redirect_uri in your app settings.
     * @param string       $mode        the API access mode, offline or per-user
     *
     * @return string formatted URL
     * @throws \Exception for missing API key
     */
    public function getAuthUrl($scopes, string $redirectUri, string $mode = 'offline'): string;
}
