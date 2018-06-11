<?php

namespace DestroBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

final class StripeACHType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('account_holder_name', null, array(
            'required' => false,
        ))->add('account_holder_type', ChoiceType::class,
            array('placeholder' => 'Please select an account type',
                'required' => true,
                'choices' => array(
                'Individual' => 'individual',
                'Company' => 'company'),
                'choices_as_values' => true, 'multiple' => false, 'expanded' => false,))
            ->add('routing_number', null, array(
                'required' => false,
            ))->add('account_number', null, array(
                'required' => false,
            ))->add('stripe_result', HiddenType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_stripe_ach';
    }

}