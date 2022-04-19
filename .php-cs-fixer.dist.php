<?php

declare(strict_types=1);

$header = <<<'HEADER'
This file is part of the Solid Client PHP project.
(c) Kévin Dunglas <kevin@dunglas.fr>
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude([
        'tests/app/var',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'header_comment' => [
            'header' => $header,
            'location' => 'after_open',
        ],
    ])
    ->setFinder($finder);
