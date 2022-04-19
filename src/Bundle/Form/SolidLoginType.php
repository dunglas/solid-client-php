<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class SolidLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'webid',
                UrlType::class,
                [
                    'label' => 'Your WebID',
                    'attr' => ['placeholder' => 'https://pod.example.org/user/profile/card#me'],
                    'constraints' => [new Url()],
                    'required' => false,
                ]
            )
            ->add(
                'op',
                UrlType::class,
                [
                    'label' => 'Or your OpenID provider',
                    'attr' => ['placeholder' => 'https://op.example.net'],
                    'constraints' => [new Url()],
                    'required' => false,
                ]
            )
            ->add('login', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new Callback(function (array $data, ExecutionContextInterface $context): void {
                $webId = $data['webid'] ?? '';
                $op = $data['op'] ?? '';

                if ('' === $webId && '' === $op) {
                    $context
                        ->buildViolation('You must provide the URL of your WebID or of your OpenID provider.')
                        ->addViolation()
                    ;

                    return;
                }

                if ('' !== $webId && '' !== $op) {
                    $context
                        ->buildViolation('Provide your WebID or your OpenID provider, not both.')
                        ->addViolation()
                    ;
                }
            })],
        ]);
    }
}
