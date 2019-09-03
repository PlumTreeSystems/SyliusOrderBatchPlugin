<?php

namespace PTS\SyliusOrderBatchPlugin\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class ExistsInArrayFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, $name, $data, array $options = []): void
    {
        if($data != '' && is_array($data) && array_key_exists('values', $data))
        {
            $dataSource->restrict($dataSource->getExpressionBuilder()->in($data['field'], $data['values']));
        }
    }
}