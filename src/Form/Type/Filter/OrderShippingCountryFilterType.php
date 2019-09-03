<?php

namespace PTS\SyliusOrderBatchPlugin\Form\Type\Filter;

use Doctrine\ORM\EntityRepository;
use Sylius\Component\Addressing\Model\Country;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderShippingCountryFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label' => 'app.filters.shippingCountry',
                'class' => Country::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.code', 'ASC');
                },
                'choice_value' => function (Country $entity = null) {
                    return $entity ? $entity->getCode() : '';
                },
                'placeholder' => 'All'
            ])
        ;
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
