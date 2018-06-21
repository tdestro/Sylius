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

class ProductVariantExtraDimension implements ProductVariantExtraDimensionInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $upsEntity;

    /**
     * @var float
     */
    protected $weight;

    /**
     * @var float
     */
    protected $width;

    /**
     * @var float
     */
    protected $height;

    /**
     * @var float
     */
    protected $depth;

    /**
     * @var int
     */
    protected $insured;

    /**
     * @var ProductVariantInterface
     */
    protected $productVariant;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * {@inheritdoc}
     */
    public function getProductVariant(): ?ProductVariantInterface
    {
        return $this->productVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductVariant(?ProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight(?float $weight): void
    {
        $this->weight = $weight;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth(): ?float
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function setWidth(?float $width): void
    {
        $this->width = $width;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeight(?float $height): void
    {
        $this->height = $height;
    }

    /**
     * {@inheritdoc}
     */
    public function getDepth(): ?float
    {
        return $this->depth;
    }

    /**
     * {@inheritdoc}
     */
    public function setDepth(?float $depth): void
    {
        $this->depth = $depth;
    }
    /**
     * {@inheritdoc}
     */
    public function getInsured(): ?int
    {
        return $this->insured;
    }

    /**
     * {@inheritdoc}
     */
    public function setInsured(?int $insured): void
    {
        $this->insured = $insured;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpsEntity(): ?string
    {
        return $this->upsEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpsEntity(?string $upsEntity): void
    {
        $this->upsEntity = $upsEntity;
    }

}
