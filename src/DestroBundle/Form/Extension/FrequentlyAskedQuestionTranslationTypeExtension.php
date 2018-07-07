<?php

declare(strict_types=1);

namespace DestroBundle\Form\Extension;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use BitBag\SyliusCmsPlugin\Form\Type\Translation\FrequentlyAskedQuestionTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class FrequentlyAskedQuestionTranslationTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('answer')
            ->add('answer', CKEditorType::class, [
                'label' => 'bitbag_sylius_cms_plugin.ui.answer',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return FrequentlyAskedQuestionTranslationType::class;
    }
}
