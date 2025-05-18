<?php

namespace Gnikyt\BasicShopifyAPI\Contracts;

use Gnikyt\BasicShopifyAPI\Options;
use GuzzleHttp\ClientInterface;

/**
 * Reprecents Guzzle client awareness.
 */
interface ClientAware
{
    /**
     * Set the Guzzle client.
     */
    public function setClient(ClientInterface $client): void;

    /**
     * Get the client.
     */
    public function getClient(): ClientInterface;

    /**
     * Set the options.
     */
    public function setOptions(Options $options): void;

    /**
     * Get the options.
     */
    public function getOptions(): Options;
}
