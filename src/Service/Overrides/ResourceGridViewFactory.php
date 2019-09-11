<?php

namespace PTS\SyliusOrderBatchPlugin\Service\Overrides;

use PTS\SyliusOrderBatchPlugin\Service\FilterManager;
use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridViewFactoryInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Resource\Metadata\MetadataInterface;

class ResourceGridViewFactory implements ResourceGridViewFactoryInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    /**
     * @var ParametersParserInterface
     */
    private $parametersParser;

    /**
     * @var FilterManager
     */
    private $filterService;

    /**
     * @param DataProviderInterface $dataProvider
     * @param ParametersParserInterface $parametersParser
     * @param FilterManager $filterService
     */
    public function __construct(
        DataProviderInterface $dataProvider,
        ParametersParserInterface $parametersParser,
        FilterManager $filterService
    )
    {
        $this->dataProvider = $dataProvider;
        $this->parametersParser = $parametersParser;
        $this->filterService = $filterService;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        Grid $grid,
        Parameters $parameters,
        MetadataInterface $metadata,
        RequestConfiguration $requestConfiguration
    ): ResourceGridView {
        $driverConfiguration = $grid->getDriverConfiguration();
        $request = $requestConfiguration->getRequest();

        $grid->setDriverConfiguration($this->parametersParser->parseRequestValues($driverConfiguration, $request));

        if(
            sizeof($parameters->all()) != 0 &&
            array_key_exists('criteria', $parameters->all()) &&
            array_key_exists('number', $parameters->all()['criteria']) &&
            array_key_exists(0, $parameters->all()['criteria']['number'])
        ) {
            $filterParameters = $parameters;

            $parameters = $this->filterService->getViewFilter($parameters);
        } else {
            $filterParameters = $parameters;
        }
        return new ResourceGridView($this->dataProvider->getData($grid, $filterParameters), $grid, $parameters, $metadata, $requestConfiguration);
    }
}