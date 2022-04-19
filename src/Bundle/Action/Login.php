<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle\Action;

use Dunglas\PhpSolidClient\Bundle\Form\SolidLoginType;
use Dunglas\PhpSolidClient\Bundle\Security\SolidAuthenticator;
use Dunglas\PhpSolidClient\OidcClient;
use Dunglas\PhpSolidClient\SolidClientFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class Login
{
    public function __construct(
        private readonly SolidClientFactory $solidClientFactory,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly bool $allowInvalidTls = false,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(SolidLoginType::class);
        $form->handleRequest($request);

        $error = $this->authenticationUtils->getLastAuthenticationError();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $oidcClient = new OidcClient($data['op'] ?? $this->solidClientFactory->create()->getOidcIssuer($data['webid']));
                if ($this->allowInvalidTls) {
                    $oidcClient->setVerifyHost(false);
                    $oidcClient->setVerifyPeer(false);
                }
                $oidcClient->register();

                $request->getSession()->set(SolidAuthenticator::OIDC_CLIENT_KEY, $oidcClient);

                $oidcClient->authenticate();
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $html = $this->twig->render('@DunglasSolidClient/login.html.twig', ['form' => $form->createView(), 'error' => $error]);

        return new Response($html, $error ? 400 : 200);
    }
}
