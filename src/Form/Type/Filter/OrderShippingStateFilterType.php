<?php

namespace PTS\SyliusOrderBatchPlugin\Form\Type\Filter;

use Sylius\Component\Core\OrderShippingStates;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderShippingStateFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => [
                    'All' => '',
                    'Cart' => OrderShippingStates::STATE_CART,
                    'Ready' => OrderShippingStates::STATE_READY,
                    'Cancelled' => OrderShippingStates::STATE_CANCELLED,
                    'Partially shipped' => OrderShippingStates::STATE_PARTIALLY_SHIPPED,
                    'Shipped' => OrderShippingStates::STATE_SHIPPED,
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
