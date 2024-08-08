<?php

namespace App\Traits;

trait MultiQueryPagination
{
    /**
     * @param int $countInterested
     * @param int $pagination
     * @param mixed $currentPage
     * @return float|int
     */
    public function getDiffCountWithPage(int $countInterested, int $pagination, mixed $currentPage): int|float
    {
        return $countInterested - ($pagination * $currentPage);
    }


    /**
     * @param $countInterested
     * @param int $perPage
     * @param mixed $currentPage
     * @return array
     */
    public function getNewLimitAndOffset($countInterested, int $perPage, mixed $currentPage): array
    {
        $interestedPageCount                   = (float) $countInterested / $perPage;
        $numOfAdminsPages    = (int)$interestedPageCount;
        $diffWithCurrentPage = $currentPage - $numOfAdminsPages;

        $limit = $perPage;
        if ($diffWithCurrentPage == 1 ) {
            $limit = $perPage - ($countInterested % $perPage);
            $limit = $limit == 0 ? $perPage : $limit;
        }

        if (($numOfAdminsPages == 0 && $diffWithCurrentPage == 1)) {
            $offset = 0;
        } else if ($countInterested < $perPage && $diffWithCurrentPage == 2){

            $offset = $perPage - $countInterested;
        } else if(($interestedPageCount - $numOfAdminsPages  )> 0.0){
            $offset = (($currentPage - 1) * $perPage) - (($countInterested) % $perPage) + ($perPage * $numOfAdminsPages);
        } else {
            if ($countInterested == 0) {
                $countInterested = 1;
            }
            $offset = (($currentPage - 1) * $perPage) - (($countInterested) % $perPage) + ($perPage * $numOfAdminsPages);

            //            $offset = $countInterested % $perPage * (($diffWithCurrentPage - 1) * $perPage);
        }

        return [$limit, $offset];
    }
}
