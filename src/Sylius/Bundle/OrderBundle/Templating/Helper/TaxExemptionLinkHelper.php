<?php

declare(strict_types=1);

namespace Sylius\Bundle\OrderBundle\Templating\Helper;
use Symfony\Component\Templating\Helper\Helper;
use Google\Cloud\Core\ServiceBuilder;
use Google\Cloud\Core\Timestamp;
use Datetime;
use DateInterval;

class TaxExemptionLinkHelper extends Helper
{

    /**
     * @param string $objectName
     *
     * @return string
     * @throws \Exception
     */
    public function getTaxExemptionLink(string $objectName): string
    {

        $signedURL = "";

        if (!empty($objectName))
        {
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

        return $signedURL;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'sylius_tax_exemption_link';
    }
}