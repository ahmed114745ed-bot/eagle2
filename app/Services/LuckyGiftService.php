<?php

namespace App\Services;

use Illuminate\Support\Collection;

class LuckyGiftService
{


    public function getRandomDuplicate(Collection $collection, int $times, ?array $properties)
    {
        if ($properties == null) $properties = [70, 20, 10];
        [$maxRow, $maxColumn] = $this->getIndexToMax($collection, $times);

        $newItems = $collection->take($maxRow + 1)->map(function ($item, $key) use ($maxRow, $maxColumn) { if($key == $maxRow) return array_slice($item ,0, $maxColumn + 1); return $item;});

        $itemCount = $newItems->count();
        if ($itemCount == 0) return 0;

        $newProperties = $this->getNewProperties($properties, $itemCount);

        $newProperties = $this->calculateCumulativeSums($newProperties);

        $rowIndexWin = $this->getWinRowIndex($newProperties);

        if ($rowIndexWin == -1) return 0;

        $winItems = $newItems[$rowIndexWin];

        return $winItems[rand(0, count($winItems) - 1)];
    }

    private function getWinRowIndex(array $property): int
    {
        $random = rand(1, 100);

        $rowIndex = -1;
        for ($i = 1; $i < count($property); $i++) {
            $currentProp = $property[$i];

            if ($property[$i - 1] <= $random && $currentProp > $random) {
                $rowIndex = $i - 1;
                break;
            }
        }
        return $rowIndex;
    }
    private  function calculateCumulativeSums(array $inputArray) : array
    {
        $cumulativeSum = 0;
        $outputArray = [0];

        foreach ($inputArray as $value) {
            $cumulativeSum += $value;
            $outputArray[] = $cumulativeSum;
        }

        return $outputArray;
    }

    private function getNewProperties(array $property, int $itemCount) : array
    {
        $propertyCount = count($property);

        if ($propertyCount <= $itemCount) {
            return $property;
        }

        $limit = $itemCount ;
        $firstToLimitArr = array_slice($property, 0, $limit);
        $outOfSlice = array_slice($property, $limit);

        if (count($outOfSlice) > 0) {
            $valueToAdd = intval(array_sum($outOfSlice) / count($firstToLimitArr));
            $firstToLimitArr = array_map(function ($item) use ($valueToAdd) {
                return $item + $valueToAdd;
            }, $firstToLimitArr);
        }

        return $firstToLimitArr;
    }

    private function getIndexToMax(Collection $collection, int $times): array
    {
        $maxRow    = -1;
        $maxColumn = -1;
        foreach ($collection as $i => $row) {
            foreach ($row as $j => $column) {
                if ($column <= $times) {
                    $maxRow    = $i;
                    $maxColumn = $j;
                }
            }
        }

        return [$maxRow, $maxColumn];
    }
}
