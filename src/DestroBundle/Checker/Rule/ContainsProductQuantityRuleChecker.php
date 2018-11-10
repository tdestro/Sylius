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

namespace DestroBundle\Checker\Rule;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Exception\UnsupportedTypeException;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

final class ContainsProductQuantityRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'contains_product_quantity';

    /**
     * {@inheritdoc}
     *
     * @throws UnsupportedTypeException
     */
    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        if (!$subject instanceof OrderInterface) {
            throw new UnsupportedTypeException($subject, OrderInterface::class);
        }

        /** @var OrderItemInterface $item */
        foreach ($subject->getItems() as $item) {
            if ($configuration['product_code'] === $item->getProduct()->getCode() && ($configuration['count'] === $item->getQuantity() || ($configuration['greaterThan'] && $item->getQuantity() > $configuration['count']))) {
                return true;
            }
        }

        return false;
    }
}
