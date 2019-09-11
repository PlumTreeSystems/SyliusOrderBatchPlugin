<?php

namespace PTS\SyliusOrderBatchPlugin\Service\Overrides;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Filtering\FilterInterface;
use Sylius\Component\Grid\Filtering\FiltersApplicatorInterface;
use Sylius\Component\Grid\Filtering\FiltersCriteriaResolverInterface;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Registry\ServiceRegistryInterface;

class FiltersApplicator implements FiltersApplicatorInterface
{
    /**
     * @var ServiceRegistryInterface
     */
    private $filtersRegistry;

    /**
     * @var FiltersCriteriaResolverInterface
     */
    private $criteriaResolver;

    /**
     * @param ServiceRegistryInterface $filtersRegistry
     * @param FiltersCriteriaResolverInterface $criteriaResolver
     */
    public function __construct(
        ServiceRegistryInterface $filtersRegistry,
        FiltersCriteriaResolverInterface $criteriaResolver
    ) {
        $this->filtersRegistry = $filtersRegistry;
        $this->criteriaResolver = $criteriaResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DataSourceInterface $dataSource, Grid $grid, Parameters $parameters): void
    {
        if (!$this->criteriaResolver->hasCriteria($grid, $parameters)) {
            return;
        }

        $criteria = $this->criteriaResolver->getCriteria($grid, $parameters);

        if(array_key_exists('number', $criteria) && array_key_exists(0, $criteria['number'])) {
            foreach ($criteria as $name => $data) {
                foreach ($data as $item) {
                    if (!$grid->hasFilter($name)) {
                        continue;
                    }

                    $gridFilter = $grid->getFilter($name);

                    /** @var FilterInterface $filter */
                    $filter = $this->filtersRegistry->get($gridFilter->getType());
                    $filter->apply($dataSource, $name, $item, $gridFilter->getOptions());
                }
            }
        } else {
            foreach ($criteria as $name => $data) {
                if (!$grid->hasFilter($name)) {
                    continue;
                }

                $gridFilter = $grid->getFilter($name);

                /** @var FilterInterface $filter */
                $filter = $this->filtersRegistry->get($gridFilter->getType());
                $filter->apply($dataSource, $name, $data, $gridFilter->getOptions());
            }
        }
    }
}
