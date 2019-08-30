<?php

declare(strict_types=1);

namespace PTS\SyliusOrderBatchPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class PTSSyliusOrderBatchPlugin extends Bundle
{
    use SyliusPluginTrait;
}
