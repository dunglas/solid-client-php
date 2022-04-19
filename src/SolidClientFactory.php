<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
