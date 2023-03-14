<?php

namespace CubaDevOps\Skeleton\Domain\Interfaces;


use Db;

interface MigrationInterface
{
    public static function getTargetVersion(): string;

    public static function up(Db $connection): bool;
    public static function down(Db $connection): bool;
}