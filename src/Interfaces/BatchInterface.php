<?php

namespace PTS\SyliusOrderBatchPlugin\Interfaces;

class BatchInterface
{
    public const OUT_OF_STOCK_TYPE = 'outOfStock';
    public const SHIPMENT_TYPE = 'shipment';
    public const FAILED_PAYMENT_TYPE = 'paymentFailed';
}