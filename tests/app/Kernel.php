<?php

use Dunglas\PhpSolidClient\Bundle\DunglasSolidClientBundle;
use Dunglas\PhpSolidClient\Bundle\Security\SolidAuthenticator;
use Dunglas\PhpSolidClient\Bundle\Security\SolidUserProvider;
use Dunglas\PhpSolidClient\Bundle\SolidClientFactory;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Twig\Environment;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SecurityBundle();
        yield new TwigBundle();
        yield new DunglasSolidClientBundle();
        yield new DebugBundle();
        yield new WebProfilerBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $frameworkConfig = [

            'secret' => 'demo',
            'session' => null,
            'profiler' => [
                'only_exceptions' => false,
            ],
        ];

        if ('test' === $this->environment) {
            $frameworkConfig['test'] = true;
            $frameworkConfig['session'] = ['storage_factory_id' => 'session.storage.factory.mock_file'];
        }

        $container->extension('framework', $frameworkConfig);
        $container->extension('web_profiler', ['toolbar' => true]);
        $container->extension('security', [
            'enable_authenticator_manager' => true,
            'password_hashers' => [
                PasswordAuthenticatedUserInterface::class => 'auto',
            ],
            'providers' => [
                'solid' => ['id' => SolidUserProvider::class],
            ],
            'firewalls' => [
                'dev' => [
                    'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                    'security' => false,
                ],
                'main' => [
                    'lazy' => true,
                    'provider' => 'solid',
                    'custom_authenticators' => [SolidAuthenticator::class],
                    'logout' => [
                        'path' => 'app_logout',
                    ]
                ]
            ],
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes
            ->add('app_homepage', '/')->controller([$this, 'homepage'])
            ->add('app_logout', '/logout')
            ->add('kevin_public_profile', '/kevin')->controller([$this, 'kevin'])
        ;
        $routes->import('@DunglasSolidClientBundle/Resources/config/routes.php');
        $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml')->prefix('/_wdt');
        $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml')->prefix('/_profiler');
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function homepage(Environment $twig): Response
    {
        return new Response(
            $twig->render('homepage.html.twig')
        );
    }

    public function kevin(Environment $twig, SolidClientFactory $solidClientFactory): Response
    {
        $client = $solidClientFactory->create();

        $webId = 'https://pod.inrupt.com/dunglas/profile/card#me';
        $profile = $client->getProfile($webId);

        return new Response($twig->render('kevin.html.twig', ['webId' => $webId, 'profile' => $profile]));
    }
}
