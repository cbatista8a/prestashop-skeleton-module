<?php

namespace CubaDevOps\Skeleton\Domain;

use CubaDevOps\Skeleton\Domain\Interfaces\MigrationInterface;
use CubaDevOps\Skeleton\Domain\ValueObjects\SortDirection;

class MigrationSorter
{
    /**
     * @var MigrationInterface[]
     */
    private $migrations;

    public function __construct(array $migrations)
    {
        $this->migrations = $migrations;
    }

    /**
     * @return array|MigrationInterface[]
     */
    public function getMigrations(): array
    {
        return $this->migrations;
    }

    public function sort(SortDirection $direction): array
    {
        $operator = $direction->getDirection() == SortDirection::ASC ? '<' : '>';
        usort($this->migrations, function ($a,$b) use ($operator){
            if (VersionComparator::compare($a::getTargetVersion(), $b::getTargetVersion(),'=')) {
                return 0;
            }
            return (VersionComparator::compare($a::getTargetVersion(), $b::getTargetVersion(),$operator)) ? -1 : 1;
        });
        return $this->migrations;
    }
}