<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\LocaleBundle\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;
use Sylius\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;

final class LocaleType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', \Symfony\Component\Form\Extension\Core\Type\LocaleType::class, [
                'label' => 'sylius.form.locale.name',
            ])
            ->add('channel', ChannelChoiceType::class, [
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_locale';
    }
}
