<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI\Contracts;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Uri;

/**
 * Reprecents Graph client.
 */
interface GraphRequester extends LimitAccesser, TimeAccesser, SessionAware, ClientAware
{
    /**
     * Runs a request to the Shopify API.
     *
     * @param string $query     the GraphQL query
     * @param array  $variables the optional variables for the query
     * @param bool   $sync      optionally wait for the request to finish
     *
     * @return array|Promise
     */
    public function request(string $query, array $variables = [], bool $sync = true);

    /**
     * Returns the base URI to use.
     *
     * @throws \Exception for missing shop domain
     */
    public function getBaseUri(): Uri;
}
