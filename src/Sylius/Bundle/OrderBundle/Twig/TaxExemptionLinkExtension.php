<?php

namespace Sylius\Bundle\OrderBundle\Twig;

use Sylius\Bundle\OrderBundle\Templating\Helper\TaxExemptionLinkHelper;


final class TaxExemptionLinkExtension extends \Twig_Extension
{
    /**
     * @var TaxExemptionLinkHelper
     */
    private $taxExemptionLinkHelper;

    /**
     * @param TaxExemptionLinkHelper $taxExemptionLinkHelper
     */
    public function __construct(TaxExemptionLinkHelper $taxExemptionLinkHelper)
    {
        $this->taxExemptionLinkHelper = $taxExemptionLinkHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_Function('tax_exemption_link', [$this->taxExemptionLinkHelper, 'getTaxExemptionLink']),
        ];
    }
}