<?php

namespace CubaDevOps\Skeleton\EventSubscriber;


use CubaDevOps\Skeleton\Application\MigrationsLoader;
use CubaDevOps\Skeleton\Domain\Interfaces\MigrationInterface;
use CubaDevOps\Skeleton\Domain\ValueObjects\SortDirection;
use Db;
use PrestaShopBundle\Event\ModuleManagementEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallerManager implements EventSubscriberInterface
{
    const UP = 1;
    const DOWN = 2;
    /**
     * @var MigrationsLoader
     */
    private $migrations_loader;

    public function __construct(MigrationsLoader $migrations_loader)
    {
        $this->migrations_loader = $migrations_loader;
    }

    /**
     * @param MigrationInterface[] $migrations
     * @param int $operation
     * @return bool
     */
    private function executeMigrations(array $migrations, int $operation = self::UP): bool
    {
        $db = Db::getInstance();
        $result = true;
        foreach ($migrations as $migration){
            $result = $result && ($operation == self::UP ? $migration::up($db) : $migration::down($db));
        }
        return $result;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ModuleManagementEvent::UNINSTALL => 'onUninstall',
            ModuleManagementEvent::UPGRADE => 'onUpgrade'
        ];
    }

    public function onUninstall(ModuleManagementEvent $event)
    {
        $module = $event->getModule();
        if ('skeleton' === $module->get('name')) {
            $migrations = $this->migrations_loader->filterByVersion(
                $module->get('version'),
                '<=',
                new SortDirection(SortDirection::DESC)
            );
            return $this->executeMigrations($migrations, self::DOWN);
        }

    }

    public function onUpgrade(ModuleManagementEvent $event)
    {
        $module = $event->getModule();
        if ('skeleton' === $module->get('name')) {
            $migrations = $this->migrations_loader->filterByVersion(
                $module->get('version_available'),
                '=',
                new SortDirection(SortDirection::ASC)
            );
            return $this->executeMigrations($migrations);
        }

    }

    /**
     * This method is not called from ModuleManagementEvent::INSTALL Event because services wasn't load yet when module in not active
     * @param string $version
     * @return bool
     */
    public function onInstall(string $version): bool
    {
        $migrations = $this->migrations_loader->filterByVersion(
            $version,
            '<=',
            new SortDirection(SortDirection::ASC)
        );
        return $this->executeMigrations($migrations);
    }
}