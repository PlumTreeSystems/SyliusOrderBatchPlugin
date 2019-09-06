<?php


namespace PTS\SyliusOrderBatchPlugin\Service;


use Pagerfanta\Pagerfanta;

class PaginatorManager
{
    public function __construct() {}

    public function calculatePages(Pagerfanta $paginator) {
        $res = [
            'totalPages' => 0,
            'previousPages' => 0,
            'nextPages' => 0
        ];

        $displayPageAmount = 6;
        $amount = $paginator->count();
        $current = $paginator->getCurrentPage();
        $maxPerPage = $paginator->getMaxPerPage();

        $res['totalPages'] = ceil($amount / $maxPerPage);

        while ($displayPageAmount != 0) {
            if ($current - $res['previousPages'] != 1 && $displayPageAmount != 0) {
                $res['previousPages'] += 1;
                $displayPageAmount -= 1;
            }

            if ($current + $res['nextPages'] != $res['totalPages'] && $displayPageAmount != 0) {
                $res['nextPages'] += 1;
                $displayPageAmount -= 1;
            }

            if ($current - $res['previousPages'] == 1 && $current + $res['nextPages'] == $res['totalPages']) {
                $displayPageAmount = 0;
            }
        }


        return $res;
    }
}