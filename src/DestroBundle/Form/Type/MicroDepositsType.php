<?php

declare(strict_types=1);

namespace DestroBundle\Form\Type;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Sylius\Bundle\MoneyBundle\Form\Type\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

final class MicroDepositsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_deposit', MoneyType::class, [
                'label' => 'First Deposit',
                'required' => true,
                'currency' => 'USD',
                'attr' => array('placeholder' => '0.00'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan([
                        'value' => 0,
                    ]),
                    new LessThan([
                        'value' => 100,
                    ]),
                ],
            ])
            ->add('second_deposit', MoneyType::class, [
                'label' => 'Second Deposit',
                'required' => true,
                'currency' => 'USD',
                'attr' => array('placeholder' => '0.00'),
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan([
                        'value' => 0,
                    ]),
                    new LessThan([
                        'value' => 100,
                    ]),
                ],
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_micro_deposits';
    }


}