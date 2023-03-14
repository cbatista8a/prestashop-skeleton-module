<?php

namespace CubaDevOps\Skeleton\Application;

use CubaDevOps\Skeleton\Domain\Interfaces\MigrationInterface;
use CubaDevOps\Skeleton\Domain\MigrationSorter;
use CubaDevOps\Skeleton\Domain\ValueObjects\SortDirection;
use CubaDevOps\Skeleton\Domain\VersionComparator;
use PhpToken;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MigrationsLoader
{
    /**
     * @var string
     */
    private $migration_path;

    public function __construct()
    {
        $this->migration_path = _PS_MODULE_DIR_.'skeleton/src/Migrations/';
    }

    /**
     * @param string $current_version
     * @param string $operator
     * The possible operators are: <, lt, <=, le, >, gt, >=, ge, ==, =, eq, !=, <>, ne respectively.
     * This parameter is case-sensitive, values should be lowercase.
     * @return MigrationInterface[]
     */
    public function filterByVersion(string $current_version,string $operator,SortDirection $order): array
    {
        $finder = Finder::create()->files()->name('*.php')->notName('index.php')->in($this->migration_path);
        $migrations = [];
        foreach ($finder->getIterator() as $migration_file){
            /** @var MigrationInterface $migration */
            $migration = $this->getAbsoluteClassNameFromFile($migration_file);
            try {
                if (VersionComparator::compare($migration::getTargetVersion(), $current_version, $operator)) {
                    $migrations[] = $migration;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return (new MigrationSorter($migrations))->sort($order);
    }

    /**
     * this method avoid collisions with other modules
     * @param SplFileInfo $file
     * @return string
     */
    private function getAbsoluteClassNameFromFile(SplFileInfo $file): string
    {
        $tokens = PhpToken::tokenize($file->getContents());
        $namespace = [];
        foreach ($tokens as $index => $token) {
            if ($token->is(T_NAMESPACE) && $tokens[$index + 2]->is(T_STRING)) {
                for ($i = $index + 2; !$tokens[$i]->is(T_WHITESPACE); $i++) {
                    if ($tokens[$i]->text === ';') {
                        continue;
                    }
                    $namespace[] = $tokens[$i]->text;
                }
                unset($tokens);
                return implode('', $namespace) . "\\" . $this->getFilenameWithoutExtension($file);
            }
        }
        return "\\" . $this->getFilenameWithoutExtension($file);
    }

    public function getFilenameWithoutExtension(SplFileInfo $file): string
    {
        return pathinfo($file->getFilename(), \PATHINFO_FILENAME);
    }
}