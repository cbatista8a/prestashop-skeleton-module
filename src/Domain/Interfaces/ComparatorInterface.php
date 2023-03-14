<?php

namespace CubaDevOps\Skeleton\Domain\Interfaces;

interface ComparatorInterface
{
    /**
     * @param $value_1
     * @param $value_2
     * @param $criteria
     * The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively.
     * This parameter is case-sensitive, values should be lowercase.
     * @return mixed
     */
    public static function compare($value_1, $value_2, $criteria);
}