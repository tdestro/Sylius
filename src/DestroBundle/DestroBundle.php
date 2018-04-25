<?php

declare(strict_types=1);

namespace DestroBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DestroBundle extends Bundle
{

    public function getParent()
    {
        return 'SyliusShopBundle';
    }
}