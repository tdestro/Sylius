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

namespace Sylius\Component\Taxation\Calculator;

use Sylius\Component\Taxation\Model\TaxRateInterface;
use Sylius\Component\Order\Context\CompositeCartContext;

final class DefaultCalculator implements CalculatorInterface
{
    private $cart;

    public function __construct(CompositeCartContext $cart)
    {
        $this->cart = $cart->getCart();
    }

    /**
     * {@inheritdoc}
     */
    public function calculate(float $base, TaxRateInterface $rate): float
    {
        if (!empty($this->cart->getTaxExemption())){
            return 0.0;
        }

        if ($rate->isIncludedInPrice()) {
            return round($base - ($base / (1 + $rate->getAmount())));
        }

        return round($base * $rate->getAmount());
    }
}
