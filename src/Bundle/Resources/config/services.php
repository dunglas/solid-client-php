<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Dunglas\PhpSolidClient\Bundle\Action\Login;
use Dunglas\PhpSolidClient\Bundle\Form\SolidLoginType;
use Dunglas\PhpSolidClient\Bundle\Security\LogoutListener;
use Dunglas\PhpSolidClient\Bundle\Security\SolidAuthenticator;
use Dunglas\PhpSolidClient\Bundle\Security\SolidUserProvider;
use Dunglas\PhpSolidClient\Bundle\SolidClientFactory as BundleSolidClientFactory;
use Dunglas\PhpSolidClient\SolidClientFactory;

return static function (ContainerConfigurator $container): void {
    $container
        ->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
        ->set(Login::class)
            ->public()
        ->set(SolidLoginType::class)
        ->set(SolidAuthenticator::class)
        ->set(SolidUserProvider::class)
        ->set(LogoutListener::class)
        ->set(SolidClientFactory::class)
        ->set(BundleSolidClientFactory::class)
        ;
};
