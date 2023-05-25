<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
class SmokeTest extends WebTestCase
{
    public function testPublicProfile(): void
    {
        $client = static::createClient();
        $client->request('GET', '/kevin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'https://id.inrupt.com/dunglas');
    }
}
