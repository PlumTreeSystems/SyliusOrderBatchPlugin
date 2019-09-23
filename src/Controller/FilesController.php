<?php

namespace PTS\SyliusOrderBatchPlugin\Controller;

use PTS\SyliusOrderBatchPlugin\Entity\Batch;
use Sylius\Component\Core\Model\Order;
use PTS\SyliusOrderBatchPlugin\Repository\BatchRepository;
use PTS\SyliusOrderBatchPlugin\Repository\OrderRepository;
use PTS\SyliusOrderBatchPlugin\Service\FilesExportingService;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilesController extends Controller
{
    public function exportCSVAction($id)
    {
        $container = $this->container;

        /** @var BatchRepository $batchRepo */
        $batchRepo = $this->getDoctrine()->getRepository(Batch::class);

        /** @var Batch $batch */
        $batch = $batchRepo->find($id);

        $response = new StreamedResponse(function () use ($container, $id, $batchRepo) {

            $batchOrdersIds = $batchRepo->getBatchOrders($id);
            $batchOrders = array_map(function ($array) {
                return $array['id'];
            }, $batchOrdersIds);

            /** @var FilesExportingService $filesExporting */
            $filesExporting = $this->get('app.file_exporting.manager');
            $page = 1;

            /** @var OrderRepository $orderRepo */
            $orderRepo = $this->getDoctrine()->getRepository(Order::class);
            $qb = $orderRepo->getPaginatedBatchOrders($batchOrders);
            $adapter = new DoctrineORMAdapter($qb);
            $pagerfanta = new Pagerfanta($adapter);
            $pagerfanta->setMaxPerPage(300);
            $pagerfanta->setCurrentPage($page);
            $hasNextPage = true;

            $file = fopen('php://output', 'w');
            $filesExporting->addOrdersTableHeaderToCSV($file);

            if ($pagerfanta->count() > 0) {
                while ($hasNextPage) {
                    /** @var \ArrayIterator $data */
                    $data = $pagerfanta->getCurrentPageResults();
                    $nextEntity = true;

                    while ($nextEntity) {
                        $entity = $data->current();
                        $data->next();

                        $filesExporting->addOrderRowToCSV($file, $entity);
                        $nextEntity = !is_null($data->current());
                    }

                    $hasNextPage = $pagerfanta->hasNextPage();

                    if ($hasNextPage) {
                        $page++;
                        $pagerfanta->setCurrentPage($page);
                    }
                }
            }

            fclose($file);
            exit;
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $batch->getName() . '.csv"');

        $response->send();
    }
}
