<?php


namespace PTS\SyliusOrderBatchPlugin\Controller;

use PTS\SyliusOrderBatchPlugin\Entity\Batch;
use PTS\SyliusOrderBatchPlugin\Entity\Filter;
use PTS\SyliusOrderBatchPlugin\Repository\BatchRepository;
use PTS\SyliusOrderBatchPlugin\Repository\FilterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController extends AbstractController
{
    public function availableBatchesAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            /** @var BatchRepository $batchRepo */
            $batchRepo = $this->getDoctrine()->getRepository(Batch::class);
            $batches = $batchRepo->getBatchesSearch();

            return new JsonResponse(json_encode($batches, true));
        }
    }

    public function availableFiltersAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            /** @var FilterRepository $filtersRepo */
            $filtersRepo = $this->getDoctrine()->getRepository(Filter::class);
            $filters = $filtersRepo->getFiltersForSearch();

            return new JsonResponse(json_encode($filters, true));
        }
    }

}
