<?php

namespace PTS\SyliusOrderBatchPlugin\Form\Type\Filter;

use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderStateFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'choices' => [
                    'All' => '',
                    'New' => OrderInterface::STATE_NEW,
                    'Cart' => OrderInterface::STATE_CART,
                    'Fulfilled' => OrderInterface::STATE_FULFILLED,
                    'Cancelled' => OrderInterface::STATE_CANCELLED
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