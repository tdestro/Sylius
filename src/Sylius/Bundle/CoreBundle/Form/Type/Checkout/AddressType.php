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

namespace Sylius\Bundle\CoreBundle\Form\Type\Checkout;

use Sylius\Bundle\AddressingBundle\Form\Type\AddressType as SyliusAddressType;
use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerGuestType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Customer\Model\CustomerAwareInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints;
use Webmozart\Assert\Assert;
use Sylius\Component\Order\Context\CompositeCartContext;
use Google\Cloud\Core\ServiceBuilder;
use Symfony\Component\Form\Extension\Core\Type\FileType;

final class AddressType extends AbstractResourceType
{
    private $cart;

    public function __construct(string $dataClass, array $validationGroups = [], CompositeCartContext $cart)
    {
        parent::__construct($dataClass, $validationGroups);
        $this->cart = $cart->getCart();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('shippingAddress', SyliusAddressType::class, [
                'shippable' => true,
                'constraints' => [new Valid()],
            ])
            ->add('billingAddress', SyliusAddressType::class, [
                'constraints' => [new Valid()],
            ])
            ->add('differentBillingAddress', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'sylius.form.checkout.addressing.different_billing_address',
            ])
            ->add('taxExemption', FileType::class, [
                'mapped' => false,
                'label' => 'Upload a tax exempt document (for tax exempt organizations).',
                'required' => false,
                'constraints' => [
                    new Constraints\File([
                        'groups' => array('sylius'),
                        'maxSize' => '20m',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf'
                        ],
                        'mimeTypesMessage' => 'Upload a pdf.'
                    ])
                ]
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
                $form = $event->getForm();
                $resource = $event->getData();
                $customer = $options['customer'];

                Assert::isInstanceOf($resource, CustomerAwareInterface::class);
                /** @var CustomerInterface $resourceCustomer */
                $resourceCustomer = $resource->getCustomer();

                if (
                    (null === $customer && null === $resourceCustomer) ||
                    (null !== $resourceCustomer && null === $resourceCustomer->getUser())
                ) {
                    $form->add('customer', CustomerGuestType::class, ['constraints' => [new Valid()]]);
                }
            })
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
                $orderData = $event->getData();

                if (isset($orderData['shippingAddress']) && (!isset($orderData['differentBillingAddress']) || false === $orderData['differentBillingAddress'])) {
                    $orderData['billingAddress'] = $orderData['shippingAddress'];

                    $event->setData($orderData);
                }

                if (isset($orderData['taxExemption'])) {
                    /** @var UploadedFile $file */
                    $uploadedFile = $orderData['taxExemption'];
                    $objectName = $this->cart->getId() . "_" . md5(uniqid()) . '.' . $uploadedFile->guessExtension();
                    copy($uploadedFile->getPathname(),"/tmp/".$objectName);

                    if (!empty($this->cart->getTaxExemption())) $objectName = $objectName ."|". $this->cart->getTaxExemption();

                    $this->cart->setTaxExemption($objectName);
                }
            })->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $orderData = $event->getData();

                if (!empty($orderData->getTaxExemption())) {

                    $TaxExPipeDel = $orderData->getTaxExemption();
                    $TaxEx = explode('|', $TaxExPipeDel);
                    $source = "/tmp/".$TaxEx[0];

                    if ($event->getForm()->isValid()) {
                        $objectName = $TaxEx[0];
                        $this->cart->setTaxExemption($objectName);

                        $gcloud = new ServiceBuilder([
                            'keyFilePath' => $_ENV['GoogleCloudStorageKeyFilePath'],
                            'projectId' => $_ENV['GoogleCloudProjectID']
                        ]);

                        $storage = $gcloud->storage();
                        $bucket = $storage->bucket('taxexemptionpdfs');

                        $object = $bucket->object($objectName);
                        if (!$object->exists()) {
                            $file = fopen($source, 'r');
                            $bucket->upload($file, [
                                'name' => $objectName
                            ]);

                            if (!empty($TaxEx[1])) {
                                $objectNameDel = $TaxEx[1];
                                $objectDel = $bucket->object($objectNameDel);
                                if ($objectDel->exists()) $objectDel->delete();
                            }
                        }
                    } else if (!empty($TaxEx[1])) {
                        $objectName = $TaxEx[1];
                        $this->cart->setTaxExemption($objectName);
                    }

                    if (file_exists ($source)) {
                        unlink($source);
                    }

                }


            });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'customer' => null,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sylius_checkout_address';
    }
}
