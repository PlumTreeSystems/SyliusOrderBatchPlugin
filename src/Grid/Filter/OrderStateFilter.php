<?php

namespace PTS\SyliusOrderBatchPlugin\Grid\Filter;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;

class OrderStateFilter implements FilterInterface
{
    public function apply(DataSourceInterface $dataSource, $name, $data, array $options = []): void
    {
        if($data != '')
        {
            $dataSource->restrict($dataSource->getExpressionBuilder()->equals('state', $data));
        }
    }
}