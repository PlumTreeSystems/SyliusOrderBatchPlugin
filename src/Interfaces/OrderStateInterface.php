<?php

namespace PTS\SyliusOrderBatchPlugin\Interfaces;


class OrderStateInterface
{
    public const NEW = 'new';
    public const PAID = 'paid';
    public const CREATED = 'created';
    public const NOT_CREATED = 'notCreated';
    public const COMPLETED = 'completed';

    public const ORDER_STATE_PARENT = 'parent';
    public const ORDER_STATE_AUTOSHIP = 'autoship';

    public const ERROR_NO_PAYMENT_METHOD_SELECTED = 'noPaymentMethodSelected';
    public const ERROR_NOT_ENOUGH_STOCK = 'notEnoughStock';
    public const ERROR_FAILED_TO_CREATE = 'failedToCreate';
}
