<?php

declare(strict_types=1);

namespace Gnikyt\BasicShopifyAPI\Traits;

use Gnikyt\BasicShopifyAPI\ResponseAccess;
use Psr\Http\Message\StreamInterface;

/**
 * Handles transforming JSON response into response.
 */
trait ResponseTransform
{
    /**
     * @see Respondable::toResponse
     */
    public function toResponse(StreamInterface $body): ResponseAccess
    {
        $decoded = json_decode(
            json: (string) $body,
            associative: true,
            flags: JSON_BIGINT_AS_STRING
        );
        return new ResponseAccess($decoded);
    }
}
