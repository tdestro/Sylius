<?php

declare(strict_types=1);

namespace DestroBundle\Form\Extension;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use BitBag\SyliusCmsPlugin\Form\Type\Translation\PageTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class PageTranslationTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('content')
            ->add('content', CKEditorType::class, [
                'label' => 'bitbag_sylius_cms_plugin.ui.content',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return PageTranslationType::class;
    }
}
