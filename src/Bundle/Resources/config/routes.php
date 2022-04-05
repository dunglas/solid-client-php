<?php

declare(strict_types=1);

use Dunglas\PhpSolidClient\Bundle\Action\Login;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('solid_client_login', '/login')
        ->controller(Login::class)
        ->methods(['GET', 'HEAD', 'POST']);
};
