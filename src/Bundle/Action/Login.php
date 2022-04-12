<?php

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle\Action;

use Dunglas\PhpSolidClient\Bundle\Form\SolidLoginType;
use Dunglas\PhpSolidClient\Bundle\Form\WebIdLoginType;
use Dunglas\PhpSolidClient\Bundle\Security\SolidAuthenticator;
use Dunglas\PhpSolidClient\OidcClient;
use Dunglas\PhpSolidClient\Profile;
use Dunglas\PhpSolidClient\SolidClientFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
