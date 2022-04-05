<?php

declare(strict_types=1);

namespace Dunglas\PhpSolidClient;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class SolidClientFactory
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function create(?OidcClient $oidcClient = null): SolidClient
    {
        return new SolidClient($this->httpClient, $oidcClient);
   }
}
