<?php

namespace PTS\SyliusOrderBatchPlugin\Form\Type;

use PTS\SyliusOrderBatchPlugin\Entity\Filter;
use PTS\SyliusOrderBatchPlugin\Form\Type\Filter\OrderPaymentStateFilterType;
use PTS\SyliusOrderBatchPlugin\Form\Type\Filter\OrderShippingCountryFilterType;
use PTS\SyliusOrderBatchPlugin\Form\Type\Filter\OrderShippingStateFilterType;
use PTS\SyliusOrderBatchPlugin\Form\Type\Filter\OrderStateFilterType;
use Sylius\Bundle\GridBundle\Form\Type\Filter\DateFilterType;
use Sylius\Bundle\GridBundle\Form\Type\Filter\StringFilterType;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Currency\Model\Currency;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /** @var $filter Filter */
    private $filter;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filterName', TextType::class, [
                'required' => true,
            ])
            ->add('number', StringFilterType::class, [
                'required' => false,
            ])
            ->add('customer', StringFilterType::class, [
                'required' => false,
            ])
            ->add('date', DateFilterType::class, [
                'required' => false,
            ])
            ->add('channel', EntityType::class, [
                'required' => false,
                'class' => Channel::class,
                'placeholder' => 'All',
                'choice_value' => function ($entity = null) {
                    if ($entity instanceof Channel) {
                        return $entity ? $entity->getId() : '';
                    } else {
                        return $entity;
                    }
                },
            ])
            ->add('totalGreaterThan', TextType::class, [
                'required' => false,
            ])
            ->add('totalLessThan', TextType::class, [
                'required' => false,
            ])
            ->add('totalCurrency', EntityType::class, [
                'class' => Currency::class,
                'required' => false,
            ])
            ->add('orderState', OrderStateFilterType::class, [
                'required' => false,
            ])
            ->add('paymentState', OrderPaymentStateFilterType::class, [
                'required' => false,
            ])
            ->add('shippingState', OrderShippingStateFilterType::class, [
                'required' => false,
            ])
            ->add('shippingCountry', OrderShippingCountryFilterType::class, [
                'required' => false,
            ])
        ;

        $builder->addViewTransformer(new CallbackTransformer(
                function (Filter $entity) {
                    $this->filter = $entity;
                    $res = [];
                    $res['number'] = ['type' => $entity->getNumberType(), 'value' => $entity->getNumberValue()];
                    $res['filterName'] = $entity->getFilterName();
                    $res['customer'] = ['type' => $entity->getCustomerType(), 'value' => $entity->getCustomerValue()];
                    $res['date'] = [
                        'from' => $entity->getDateFrom(),
                        'to' => $entity->getDateTo()
                    ];
                    $res['channel'] = $entity->getChannel();
                    $res['totalGreaterThan'] = $entity->getTotalGreaterThan();
                    $res['totalLessThan'] = $entity->getTotalLessThan();
                    $res['totalCurrency'] = $entity->getTotalCurrency();
                    $res['orderState'] = $entity->getOrderState();
                    $res['shippingState'] = $entity->getShippingState();
                    $res['paymentState'] = $entity->getPaymentState();
                    $res['shippingCountry'] = $entity->getShippingCountry();
                    return $res;
                },
                function ($entity) {
                    $this->filter->setNumberType($entity['number']['type']);
                    $this->filter->setNumberValue($entity['number']['value']);
                    $this->filter->setFilterName($entity['filterName']);
                    $this->filter->setCustomerType($entity['customer']['type']);
                    $this->filter->setCustomerValue($entity['customer']['value']);

                    $this->filter->setDateFrom($entity["date"]["from"]);
                    $this->filter->setDateTo($entity["date"]["to"]);

                    if (is_null($entity['channel'])) {
                        $this->filter->setChannel($entity['channel']);

                    } else {
                        $this->filter->setChannel($entity['channel']->getId());
                    }
                    $this->filter->setTotalCurrency($entity['totalCurrency']);
                    $this->filter->setTotalGreaterThan($entity['totalGreaterThan']);
                    $this->filter->setTotalLessThan($entity['totalLessThan']);

                    $this->filter->setOrderState($entity['orderState']);
                    $this->filter->setPaymentState($entity['paymentState']);
                    $this->filter->setShippingState($entity['shippingState']);

                    if (is_null($entity['shippingCountry'])) {
                        $this->filter->setShippingCountry($entity['shippingCountry']);
                    } else {
                        $this->filter->setShippingCountry($entity['shippingCountry']->getCode());
                    }

                    return $this->filter;
                }
            ));
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'filter_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'allow_extra_fields' => true,
        ]);
    }
}
