<?php

namespace CubaDevOps\Skeleton\Migrations;

use CubaDevOps\Skeleton\Domain\Interfaces\MigrationInterface;
use Db;

class Version1_1_2 implements MigrationInterface
{

    public static function getTargetVersion(): string
    {
        return "1.1.2";
    }


    public static function up(Db $connection): bool
    {
        return $connection->execute(
            "alter table `skeleton` add column `date_add` timestamp default CURRENT_TIMESTAMP null,
                                        add column `date_upd` timestamp default CURRENT_TIMESTAMP null on update CURRENT_TIMESTAMP;"
        );
    }


    public static function down(Db $connection): bool
    {
        return $connection->execute(
            "alter table `skeleton` drop column `date_add`,
                                        drop column `date_upd`;"
        );
    }
}