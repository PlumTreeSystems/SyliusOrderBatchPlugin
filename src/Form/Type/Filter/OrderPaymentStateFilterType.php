<?php

namespace PTS\SyliusOrderBatchPlugin\Form\Type\Filter;

use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderPaymentStateFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => [
                    'All' => '',
                    'Cart' => OrderPaymentStates::STATE_CART,
                    'Awaiting payment' => OrderPaymentStates::STATE_AWAITING_PAYMENT,
                    'Partially authorized' => OrderPaymentStates::STATE_PARTIALLY_AUTHORIZED,
                    'Authorized' => OrderPaymentStates::STATE_AUTHORIZED,
                    'Partially paid' => OrderPaymentStates::STATE_PARTIALLY_PAID,
                    'Cancelled' => OrderPaymentStates::STATE_CANCELLED,
                    'Paid' => OrderPaymentStates::STATE_PAID,
                    'Partially refunded' => OrderPaymentStates::STATE_PARTIALLY_REFUNDED,
                    'Refunded' => OrderPaymentStates::STATE_REFUNDED,
                    ],
            ])
            ->setAllowedTypes('choices', ['array'])
        ;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
