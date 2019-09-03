<?php


namespace PTS\SyliusOrderBatchPlugin\Controller;

use PTS\SyliusOrderBatchPlugin\Repository\BatchRepository;
use PTS\SyliusOrderBatchPlugin\Repository\FilterRepository;
use FOS\RestBundle\View\View;
use PTS\SyliusOrderBatchPlugin\Entity\Batch;
use PTS\SyliusOrderBatchPlugin\Entity\Filter;
use PTS\SyliusOrderBatchPlugin\Service\BatchManager;
use PTS\SyliusOrderBatchPlugin\Service\FilterManager;
use PTS\SyliusOrderBatchPlugin\Service\PaginatorManager;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends \Sylius\Bundle\CoreBundle\Controller\OrderController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function customFiltersAction(Request $request): Response
    {
        $createFilterRes = [];
        $session = $this->get('session');

        /** @var FilterManager $filterManager */
        $filterManager = $this->get('pts_sylius_order_batch_plugin.filter.manager');

        if ($request->query->get('filterButton'))
        {
            $createFilterRes = $filterManager->createFilter($request->query->all());

            if(array_key_exists('errors', $createFilterRes))
            {
                $errors = $createFilterRes['errors'];
                foreach ($errors as $error) {
                    foreach ($error as $item) {
                        $session->getFlashBag()->add('error', $item);
                    }
                }
            } else {
                $request->query->set('criteria', $filterManager->getEmptyFilter());
            }
        }

        /** @var FilterRepository $filtersRepo */
        $filtersRepo = $this->getDoctrine()->getRepository(Filter::class);

        if (!is_null($request->query->get('filter')))
        {
            $saved = array_values($request->query->get('filter'));
            if(array_key_exists('filter', $createFilterRes))
            {
                array_push($saved, $createFilterRes['filter']);
                $request->query->set('filter', $saved);
            }

            $filters = $filtersRepo->getFilters($saved);

        } else if (array_key_exists('filter', $createFilterRes)) {
            $filters = $filtersRepo->getFilters([$createFilterRes['filter']]);
            $request->query->set('filter', [$createFilterRes['filter']]);
        } else {
            $filters = null;
        }

        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::INDEX);
        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        if (array_key_exists('batchButton', $request->query->all()))
        {
            /** @var BatchManager $batchManager */
            $batchManager = $this->get('pts_sylius_order_batch_plugin.batch.manager');
            $createBatchResponse = $batchManager->createBatchFromRequest($resources, $request);

            if(array_key_exists('errors', $createBatchResponse))
            {
                $session = $this->get('session');

                $errors = $createBatchResponse['errors'];
                foreach ($errors as $error) {
                    foreach ($error as $item) {
                        $session->getFlashBag()->add('error', $item);
                    }
                }
            } else {
                $createBatchResponse['id'];
                $path = $this->generateUrl('app_batch_show', ['id' => $createBatchResponse['id']]);
                return new RedirectResponse($path);
            }
        }

        $this->eventDispatcher->dispatchMultiple(ResourceActions::INDEX, $configuration, $resources);

        $view = View::create($resources);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate(ResourceActions::INDEX . '.html'))
                ->setTemplateVar($this->metadata->getPluralName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resources' => $resources,
                    'filters' => $filters,
                    $this->metadata->getPluralName() => $resources,
                ])
            ;
        }

        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * @param Request $request
     * @param string $id
     *
     * @return Response
     */
    public function batchAction(Request $request, $id = null): Response
    {
        $translator = $this->get('translator');
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();

        /** @var FilterManager $filterManager */
        $filterManager = $this->get('pts_sylius_order_batch_plugin.filter.manager');

        /** @var BatchRepository $batchRepo */
        $batchRepo = $this->getDoctrine()->getRepository(Batch::class);

        /** @var BatchManager $batchManager */
        $batchManager = $this->get('app.batch.manager');

        $config = $this->requestConfigurationFactory->create($this->metadata, $request);
        $grid = $config->getParameters()->get('grid');

        $type = $batchManager->resolveBatchTypeByGrid($grid);
        $batchParams = $this->container->getParameter('batch')[$type];

        if (!is_null($id)) {
            /** @var Batch $batch */
            $batch = $batchRepo->find($id);
        } else {
            /** TODO: fix if null */
            /** @var Batch $batch */
            $batch = $batchRepo->findOneBy(['type' => $type]);

            if (is_null($batch)) {
                $batch = new Batch();

                $batch->setName($batchParams['name']);
                $batch->setType($type);

                $em->persist($batch);
                $em->flush();
            }

            $id = $batch->getId();
        }

        switch ($request->query->get('_action')) {
            case 'removeOrder':
                $orderId = $request->query->get('_orderRemove');
                if (!is_null($orderId)) {
                    $batch = $batchManager->removeOrderFromBatch($orderId, $batch);
                } else {
                    $session->getFlashBag()->add(
                        'error',
                        $translator->trans('app.messages.errors.canNotDelete')
                    );
                }
                break;
        }

        $batchOrders = $batchRepo->getBatchOrders($id);

        $batchOrders = array_map(function($array) {return $array['id'];}, $batchOrders);


        $data = $request->query->all();

        if (!array_key_exists('criteria', $data))
        {
            $data = ['criteria' => $filterManager->getEmptyFilter()];
        }

        $data['criteria']['batch'] = [
            'values' => $batchOrders,
            'field' => 'id'
        ];

        $request->query->set('criteria', $data['criteria']);

        $createFilterRes = [];

        if ($request->query->get('filterButton'))
        {
            $createFilterRes = $filterManager->createFilter($request->query->all());

            if(array_key_exists('errors', $createFilterRes))
            {
                $errors = $createFilterRes['errors'];
                foreach ($errors as $error) {
                    foreach ($error as $item) {
                        $session->getFlashBag()->add('error', $item);
                    }
                }
            } else {
                $request->query->set('criteria', $filterManager->getEmptyFilter());
            }
        }

        /** @var FilterRepository $filtersRepo */
        $filtersRepo = $this->getDoctrine()->getRepository(Filter::class);

        if (!is_null($request->query->get('filter')))
        {
            $saved = array_values($request->query->get('filter'));
            if(array_key_exists('filter', $createFilterRes))
            {
                array_push($saved, $createFilterRes['filter']);
                $request->query->set('filter', $saved);
            }

            $filters = $filtersRepo->getFilters($saved);

        } else if (array_key_exists('filter', $createFilterRes)) {

            $filters = $filtersRepo->getFilters([$createFilterRes['filter']]);
            $request->query->set('filter', [$createFilterRes['filter']]);
        } else {
            $filters = null;
        }

        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::INDEX);
        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository);

        $this->eventDispatcher->dispatchMultiple(ResourceActions::INDEX, $configuration, $resources);

        $view = View::create($resources);

        $documentManager = $this->get('pts_sylius_order_batch_plugin.document.manager');

        switch ($request->query->get('_action')) {
            case 'markShipped':
                $paginator = $view->getData()->getData();
                $batchManager->markAllAsShipped($paginator);
                break;
            case 'downloadShippingNotes':
                $paginator = $view->getData()->getData();
                return $documentManager->streamDocument($paginator);
            case 'runOutOfStock':
                $session->getFlashBag()->add(
                    'success',
                    $translator->trans('app.flashMessages.outOfStockRunStarted')
                );
                break;
            case 'runFailedPayment':
                $session->getFlashBag()->add(
                    'success',
                    $translator->trans('app.flashMessages.attemptChargeStarted')
                );
                break;
        }

        /** @var PaginatorManager $paginatorManager */
        $paginatorManager = $this->get('pts_sylius_order_batch_plugin.paginator.manager');
        $pages = $paginatorManager->calculatePages($resources->getData());

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate(ResourceActions::INDEX . '.html'))
                ->setTemplateVar($this->metadata->getPluralName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resources' => $resources,
                    'filters' => $filters,
                    'batch' => $batch,
                    'paginatorPages' => $pages,
                    $this->metadata->getPluralName() => $resources,
                ])
            ;
        }

        return $this->viewHandler->handle($configuration, $view);
    }

    public function saveAction(Request $request): Response
    {
        if (!isset($request->request->get('sylius_cart')['autoship'])) {
            $cart = $request->request->get('sylius_cart');
            $cart['autoship'] = false;
            $request->request->set('sylius_cart', $cart);
        }
        return parent::saveAction($request); // TODO: Change the autogenerated stub
    }
}
