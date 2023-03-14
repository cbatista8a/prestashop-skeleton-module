<?php

namespace CubaDevOps\Skeleton\Migrations;

use CubaDevOps\Skeleton\Domain\Interfaces\MigrationInterface;
use Db;

class Version1_1_1 implements MigrationInterface
{

    public static function getTargetVersion(): string
    {
        return "1.1.1";
    }


    public static function up(Db $connection):bool
    {
        return $connection->execute(
            "CREATE TABLE IF NOT EXISTS `skeleton`(
                    `id` int(11) AUTO_INCREMENT NOT NULL,
                    `module_name` varchar(50) NOT NULL, 
                PRIMARY KEY (`id`)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;"
        );
    }


    public static function down(Db $connection):bool
    {
        return $connection->execute(
            'DROP TABLE IF EXISTS `skeleton`'
        );
    }
}