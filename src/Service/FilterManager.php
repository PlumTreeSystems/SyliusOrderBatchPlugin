<?php


namespace PTS\SyliusOrderBatchPlugin\Service;


use PTS\SyliusOrderBatchPlugin\Entity\Filter;
use PTS\SyliusOrderBatchPlugin\Form\Type\FilterType;
use PTS\SyliusOrderBatchPlugin\Repository\FilterRepository;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Symfony\Component\Form\FormFactory;
use Sylius\Component\Grid\Parameters;

/**
 * Class FilterManager
 * @package App\Service
 *
 * After adding new filter:
 *  - in createFilter function add data from request to response
 *  - add filter to existing filters in combineFilters function
 *  - add filters applied in view to getViewFilter response
 *  - add empty filter value to getEmptyFilter function
 */
class FilterManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var FormErrorExtractor
     */
    private $formErrorExtractor;

    /**
     * Filters service constructor.
     * @param EntityManager $entityManager
     * @param FormFactory $formFactory
     * @param Serializer $serializer
     * @param FormErrorExtractor $formErrorExtractor
     */
    public function __construct(
        EntityManager $entityManager,
        FormFactory $formFactory,
        Serializer $serializer,
        FormErrorExtractor $formErrorExtractor
    )
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->serializer = $serializer;
        $this->formErrorExtractor = $formErrorExtractor;
    }

    public function createFilter($parameters)
    {
        if (
            (
                $parameters['criteria']['date']['from']['date'] == "" &&
                $parameters['criteria']['date']['from']['time'] != ""
            ) || (
                $parameters['criteria']['date']['to']['date'] == "" &&
                $parameters['criteria']['date']['to']['time'] != ""
            )
        ) {
            return [];
        }

        $data = [];
        $data['filterName'] = $parameters['_filterName'];
        $data['number'] = $parameters['criteria']['number'];
        $data['customer'] = $parameters['criteria']['customer'];
        if ($parameters['criteria']['date']['from']['date'] != '') {
            $data['date']['from'] = $parameters['criteria']['date']['from'];
        }
        if ($parameters['criteria']['date']['to']['date'] != '') {
            $data['date']['to'] = $parameters['criteria']['date']['to'];
        }
        $data['channel'] = $parameters['criteria']['channel'];
        $data['totalGreaterThan'] = $parameters['criteria']['total']['greaterThan'];
        $data['totalLessThan'] = $parameters['criteria']['total']['lessThan'];
        $data['totalCurrency'] = $parameters['criteria']['total']['currency'];

        $data['orderState'] = $parameters['criteria']['state'];
        $data['paymentState'] = $parameters['criteria']['payment_state'];
        $data['shippingState'] = $parameters['criteria']['shipping_state'];
        $data['shippingCountry'] = $parameters['criteria']['shipping_country'];

        $filter = new Filter();
        $form = $this->formFactory->create(FilterType::class, $filter, [
            'csrf_protection' => false
        ]);

        $form->submit($data);
        if ($form->isValid()) {
            $this->entityManager->persist($filter);
            $this->entityManager->flush();
            return ['filter' => $filter->getId()];
        } else {
            $errors = $this->formErrorExtractor->getErrorMessages($form);
            return ['errors' => $errors];
        }
    }

    public function combineFilters($data)
    {
        if (!array_key_exists('filter', $data)) {
            $res = new Parameters($data);

            return $res;
        }

        $resData = [
            'criteria' => [
                'number' => [
                    $data['criteria']['number']
                ],
                'customer' => [
                    $data['criteria']['customer']
                ],
                'date' => [
                    $data['criteria']['date']
                ],
                'channel' => [
                    $data['criteria']['channel']
                ],
                'total' => [
                    $data['criteria']['total']
                ],
                'state' => [
                    $data['criteria']['state']
                ],
                'payment_state' => [
                    $data['criteria']['payment_state']
                ],
                'shipping_state' => [
                    $data['criteria']['shipping_state']
                ],
                'shipping_country' => [
                    $data['criteria']['shipping_country']
                ]
            ]
        ];

        if (array_key_exists('batch', $data['criteria'])) {
            $resData['criteria']['batch'] = [
                $data['criteria']['batch']
            ];
        }

        $saved = array_values($data['filter']);

        /** @var FilterRepository $filtersRepo */
        $filtersRepo = $this->entityManager->getRepository(Filter::class);
        $filters = $filtersRepo->getFilters($saved);

        foreach ($filters as $filter) {
            $filterData = $this->serializer->toArray($filter);

            if (key_exists('numberType', $filterData) && key_exists('numberValue', $filterData)) {
                array_push(
                    $resData['criteria']['number'],
                    [
                        'type' => $filterData['numberType'],
                        'value' => $filterData['numberValue']
                    ]
                );
            }

            if (key_exists('customerType', $filterData) && key_exists('customerValue', $filterData)) {
                array_push(
                    $resData['criteria']['customer'],
                    [
                        'type' => $filterData['customerType'],
                        'value' => $filterData['customerValue']
                    ]
                );
            }

            if (key_exists('channel', $filterData)) {
                array_push(
                    $resData['criteria']['channel'],
                    $filterData['channel']
                );
            }

            if (
                key_exists('totalGreaterThan', $filterData) ||
                key_exists('totalLessThan', $filterData) ||
                key_exists('totalCurrency', $filterData)
            ) {
                $total = [
                    'greaterThan' => '',
                    'lessThan' => '',
                    'currency' => '',
                ];

                if (key_exists('totalGreaterThan', $filterData)) {
                    $total['greaterThan'] = $filterData['totalGreaterThan'];
                }

                if (key_exists('totalLessThan', $filterData)) {
                    $total['lessThan'] = $filterData['totalLessThan'];
                }

                if (key_exists('totalCurrency', $filterData)) {
                    $total['currency'] = $filterData['totalCurrency'];
                }

                array_push($resData['criteria']['total'], $total);
            }

            if (key_exists('dateTo', $filterData)) {
                $date = new \DateTime($filterData['dateTo']);

                array_push(
                    $resData['criteria']['date'],
                    [
                        'to' => [
                            'date' => $date->format('Y-m-d'),
                            'time' => $date->format('H:m')
                        ],
                        'from' => [
                            'date' => '',
                            'time' => ''
                        ]
                    ]
                );
            }

            if (key_exists('dateFrom', $filterData)) {
                $date = new \DateTime($filterData['dateFrom']);

                array_push(
                    $resData['criteria']['date'],
                    [
                        'to' => [
                            'date' => '',
                            'time' => ''
                        ],
                        'from' => [
                            'date' => $date->format('Y-m-d'),
                            'time' => $date->format('H:m')
                        ]
                    ]
                );
            }

            if (key_exists('orderState', $filterData)) {
                array_push(
                    $resData['criteria']['state'],
                    $filterData['orderState']
                );
            }

            if (key_exists('paymentState', $filterData)) {
                array_push(
                    $resData['criteria']['payment_state'],
                    $filterData['paymentState']
                );
            }

            if (key_exists('shippingState', $filterData)) {
                array_push(
                    $resData['criteria']['shipping_state'],
                    $filterData['shippingState']
                );
            }

            if (key_exists('shippingCountry', $filterData)) {
                array_push(
                    $resData['criteria']['shipping_country'],
                    $filterData['shippingCountry']
                );
            }
        }

        $res = new Parameters($resData);

        return $res;
    }

    public function getViewFilter(Parameters $data): Parameters
    {
        $param = $data->all()['criteria'];
        $data = [
            'criteria' => [
                'number' => $param['number'][0],
                'customer' => $param['customer'][0],
                'total' => $param['total'][0],
                'date' => $param['date'][0],
                'channel' => $param['channel'][0],
                'state' => $param['state'][0],
                'payment_state' => $param['payment_state'][0],
                'shipping_state' => $param['shipping_state'][0],
                'shipping_country' => $param['shipping_country'][0]
            ]
        ];

        if (array_key_exists('batch', $param)) {
            $data['batch'] = $param['batch'][0];
        }

        return new Parameters($data);
    }

    public function getEmptyFilter()
    {
        $filter = [
            'number' => ['type' => '', 'value' => ''],
            'customer' => ['type' => '', 'value' => ''],
            'total' => [
                'greaterThan' => '',
                'lessThan' => '',
                'currency' => ''
            ],
            'date' => [
                'to' => [
                    'date' => '',
                    'time' => ''
                ],
                'from' => [
                    'date' => '',
                    'time' => ''
                ]
            ],
            'channel' => '',
            'state' => '',
            'payment_state' => '',
            'shipping_state' => '',
            'shipping_country' => '',
        ];

        return $filter;
    }
}
