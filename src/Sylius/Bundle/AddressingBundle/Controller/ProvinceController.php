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

namespace Sylius\Bundle\AddressingBundle\Controller;

use FOS\RestBundle\View\View;
use Sylius\Bundle\AddressingBundle\Form\Type\ProvinceCodeChoiceType;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Addressing\Model\CountryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Google\Cloud\Core\ServiceBuilder;
use Google\Cloud\Core\Timestamp;
use Datetime;
use DateInterval;
use Sylius\Component\Addressing\Model\ProvinceInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ProvinceController extends ResourceController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function choiceOrTextFieldFormAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if (!$configuration->isHtmlRequest() || null === $countryCode = $request->query->get('countryCode')) {
            throw new AccessDeniedException();
        }

        /** @var CountryInterface $country */
        if (!$country = $this->get('sylius.repository.country')->findOneBy(['code' => $countryCode])) {
            throw new NotFoundHttpException('Requested country does not exist.');
        }

        if (!$country->hasProvinces()) {
            $form = $this->createProvinceTextForm();

            $view = View::create()
                ->setData([
                    'metadata' => $this->metadata,
                    'form' => $form->createView(),
                ])
                ->setTemplate($configuration->getTemplate('_provinceText.html'))
            ;

            return new JsonResponse([
                'content' => $this->viewHandler->handle($configuration, $view)->getContent(),
            ]);
        }

        $form = $this->createProvinceChoiceForm($country);

        $view = View::create()
            ->setData([
                'metadata' => $this->metadata,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate('_provinceChoice.html'))
        ;

        return new JsonResponse([
            'content' => $this->viewHandler->handle($configuration, $view)->getContent(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function uploadFieldFormAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if (!$configuration->isHtmlRequest() || null === $provinceCode = $request->query->get('provinceCode')) {
            throw new AccessDeniedException();
        }

        /** @var ProvinceInterface $province */
        if (!$province = $this->get('sylius.repository.province')->findOneBy(['code' => $provinceCode])) {
            throw new NotFoundHttpException('Requested province code does not exist.');
        }

        if (!$province->getTaxexemptionupload()){
            return new Response();
        }


        $form = $this->createProvinceTaxExemptionUpload();
        $objectName = $this->get('sylius.context.cart')->getCart()->getTaxExemption();

        $signedURL = null;

        if(!empty($objectName)){
            $gcloud = new ServiceBuilder([
                'keyFilePath' => $_ENV['GoogleCloudStorageKeyFilePath'],
                'projectId' => $_ENV['GoogleCloudProjectID']
            ]);

            $storage = $gcloud->storage();
            $bucket = $storage->bucket('taxexemptionpdfs');

            $object = $bucket->object($objectName);
            if ($object->exists()) {
                $dateTimeObj = new DateTime();
                $dateTimeObj->add(new DateInterval("PT1H"));

                $signedURL = $object->signedUrl(new Timestamp($dateTimeObj));
            }
        }

        $view = View::create()
            ->setData([
                'metadata' => $this->metadata,
                'form' => $form->createView(),
                'taxexemptionlink' => $province->getTaxexemptionlink(),
                'signedurl' => $signedURL
            ])
            ->setTemplate($configuration->getTemplate('_taxexempt.html.twig'))
        ;
        return new JsonResponse([
            'content' => $this->viewHandler->handle($configuration, $view)->getContent(),
        ]);
    }
    /**
     * @return FormInterface
     */
    protected function createProvinceTaxExemptionUpload(): FormInterface
    {
        return $this->get('form.factory')->createNamed('sylius_address_taxexemption',  FileType::class, null, [
            'required' => false,
            'label' => 'For tax exempt organizations:',
        ]);
    }

    /**
     * @param CountryInterface $country
     *
     * @return FormInterface
     */
    protected function createProvinceChoiceForm(CountryInterface $country): FormInterface
    {
        return $this->get('form.factory')->createNamed('sylius_address_province', ProvinceCodeChoiceType::class, null, [
            'country' => $country,
            'label' => 'sylius.form.address.province',
            'placeholder' => 'sylius.form.province.select',
        ]);
    }

    /**
     * @return FormInterface
     */
    protected function createProvinceTextForm(): FormInterface
    {
        return $this->get('form.factory')->createNamed('sylius_address_province', TextType::class, null, [
            'required' => false,
            'label' => 'sylius.form.address.province',
        ]);
    }
}
