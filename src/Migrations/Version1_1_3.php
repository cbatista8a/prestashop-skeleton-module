<?php

namespace CubaDevOps\Skeleton\Migrations;

use CubaDevOps\Skeleton\Domain\Interfaces\MigrationInterface;
use Db;

class Version1_1_3 implements MigrationInterface
{

    public static function getTargetVersion(): string
    {
        return "1.1.3";
    }


    public static function up(Db $connection):bool
    {
        return \Hook::registerHook(\Module::getInstanceByName('skeleton'), 'skeletonCustomHook');
    }


    public static function down(Db $connection):bool
    {
        return \Hook::unregisterHook(\Module::getInstanceByName('skeleton'), 'skeletonCustomHook');
    }
}