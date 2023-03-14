<?php

namespace CubaDevOps\Skeleton\Domain\ValueObjects;

class SortDirection
{
    const ASC = 1;
    const DESC = 2;
    /**
     * @var int
     */
    private $direction;


    /**
     * @param int $direction | 1 = ASC, 2 = DESC
     * @throws \Exception
     */
    public function __construct(int $direction)
    {
        $this->setDirection($direction);
    }

    /**
     * @return int
     */
    public function getDirection(): int
    {
        return $this->direction;
    }

    /**
     * @param int $direction
     * @throws \Exception
     */
    private function setDirection(int $direction)
    {
        if (!in_array($direction, [self::ASC, self::DESC],true)){
            throw new \Exception("Wrong sorter direction");
        }
        $this->direction = $direction;
    }
}