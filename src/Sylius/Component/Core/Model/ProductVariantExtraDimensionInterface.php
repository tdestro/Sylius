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

namespace Sylius\Component\Core\Model;

use Sylius\Component\Resource\Model\ResourceInterface;

interface ProductVariantExtraDimensionInterface extends ResourceInterface
{
    /**
     * @return ProductVariantInterface|null
     */
    public function getProductVariant(): ?ProductVariantInterface;

    /**
     * @param ProductVariantInterface|null $productVariant
     */
    public function setProductVariant(?ProductVariantInterface $productVariant): void;

    /**
     * @return float|null
     */
    public function getWeight(): ?float;

    /**
     * @param float|null $weight
     */
    public function setWeight(?float $weight): void;

    /**
     * @return float|null
     */
    public function getWidth(): ?float;

    /**
     * @param float|null $width
     */
    public function setWidth(?float $width): void;

    /**
     * @return float|null
     */
    public function getHeight(): ?float;

    /**
     * @param float|null $height
     */
    public function setHeight(?float $height): void;

    /**
     * @return float|null
     */
    public function getDepth(): ?float;

    /**
     * @param float|null $depth
     */
    public function setDepth(?float $depth): void;
}
