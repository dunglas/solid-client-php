<?php

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle\Security;

use Dunglas\PhpSolidClient\OidcClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class LogoutListener implements EventSubscriberInterface
{
    public function __invoke(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if (!$token || !$token->hasAttribute('oidc_client')) {
            return;
        }

        /**
         * @var OidcClient
         */
        $oidcClient = $token->getAttribute('oidc_client');
        $oidcClient->signOut($oidcClient->getIdToken(), $event->getRequest()->getUri());
    }

    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => '__invoke'];
    }
}
