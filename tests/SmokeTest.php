<?php

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

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
        $this->assertSelectorTextContains('h1', 'dunglas');
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        require __DIR__.'/app/Kernel.php';

        return new \Kernel('test', true);
    }
}
