<?php

namespace PTS\SyliusOrderBatchPlugin\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ExistsInArrayFilterType extends AbstractType
{
    public function getParent()
    {
        return NumberType::class;
    }
}
