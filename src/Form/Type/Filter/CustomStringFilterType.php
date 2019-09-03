<?php

namespace PTS\SyliusOrderBatchPlugin\Form\Type\Filter;

use Sylius\Bundle\GridBundle\Form\Type\Filter\StringFilterType;
use Symfony\Component\Form\AbstractType;

class CustomStringFilterType extends AbstractType
{
    public function getParent()
    {
        return StringFilterType::class;
    }
}
