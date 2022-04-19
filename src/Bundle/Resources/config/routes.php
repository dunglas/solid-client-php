<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use Dunglas\PhpSolidClient\Bundle\Action\Login;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('solid_client_login', '/login')
        ->controller(Login::class)
        ->methods(['GET', 'HEAD', 'POST']);
};
