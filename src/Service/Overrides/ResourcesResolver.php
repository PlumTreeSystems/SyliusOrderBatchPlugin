<?php


namespace PTS\SyliusOrderBatchPlugin\Service\Overrides;

use PTS\SyliusOrderBatchPlugin\Service\FilterManager;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\ResourcesResolverInterface;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridViewFactoryInterface;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ResourcesResolver implements ResourcesResolverInterface
{
    /**
     * @var ResourcesResolverInterface
     */
    private $decoratedResolver;

    /**
     * @var GridProviderInterface
     */
    private $gridProvider;

    /**
     * @var ResourceGridViewFactoryInterface
     */
    private $gridViewFactory;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @param ResourcesResolverInterface $decoratedResolver
     * @param GridProviderInterface $gridProvider
     * @param ResourceGridViewFactoryInterface $gridViewFactory
     * @param FilterManager $filterService
     */
    public function __construct(
        ResourcesResolverInterface $decoratedResolver,
        GridProviderInterface $gridProvider,
        ResourceGridViewFactoryInterface $gridViewFactory,
        FilterManager $filterService
    ) {
        $this->decoratedResolver = $decoratedResolver;
        $this->gridProvider = $gridProvider;
        $this->gridViewFactory = $gridViewFactory;
        $this->filterManager = $filterService;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources(RequestConfiguration $requestConfiguration, RepositoryInterface $repository)
    {
        if (!$requestConfiguration->hasGrid()) {
            return $this->decoratedResolver->getResources($requestConfiguration, $repository);
        }

        $gridDefinition = $this->gridProvider->get($requestConfiguration->getGrid());

        $request = $requestConfiguration->getRequest();
        $parameters = $this->filterManager->combineFilters($request->query->all());

        $gridView = $this->gridViewFactory->create($gridDefinition, $parameters, $requestConfiguration->getMetadata(), $requestConfiguration);

        if ($requestConfiguration->isHtmlRequest()) {
            return $gridView;
        }

        return $gridView->getData();
    }
}