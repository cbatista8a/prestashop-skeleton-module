<?php

namespace CubaDevOps\Skeleton\Domain;

class VersionComparator implements Interfaces\ComparatorInterface
{

    public static function compare($value_1, $value_2, $criteria):bool
    {
        return version_compare($value_1, $value_2, $criteria);
    }
}