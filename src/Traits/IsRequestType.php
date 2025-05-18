<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI\Traits;

use Psr\Http\Message\UriInterface;

/**
 * Determine GraphQL or REST request type.
 */
trait IsRequestType
{
    /**
     * Determines if the request is to Graph API.
     */
    protected function isGraphRequest(UriInterface $uri): bool
    {
        return strpos($uri->getPath(), 'graphql.json') !== false;
    }

    /**
     * Determines if the request is to REST API.
     */
    protected function isRestRequest(UriInterface $uri): bool
    {
        return $this->isGraphRequest($uri) === false;
    }
}
