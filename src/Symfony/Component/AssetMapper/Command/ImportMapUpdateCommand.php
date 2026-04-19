<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\AssetMapper\Command;

use Symfony\Component\AssetMapper\ImportMap\ImportMapEntry;
use Symfony\Component\AssetMapper\ImportMap\ImportMapManager;
use Symfony\Component\AssetMapper\ImportMap\ImportMapVersionChecker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Kévin Dunglas <kevin@dunglas.dev>
 */
#[AsCommand(name: 'importmap:update', description: 'Update JavaScript packages to their latest versions')]
final class ImportMapUpdateCommand extends Command
{
    use VersionProblemCommandTrait;

    public function __construct(
        private readonly ImportMapManager $importMapManager,
        private readonly ImportMapVersionChecker $importMapVersionChecker,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('packages', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'List of packages\' names')
            ->setHelp(<<<'EOT'
                The <info>%command.name%</info> command will update all from the 3rd part packages
                in <comment>importmap.php</comment> to their latest version, including downloaded packages.

                   <info>php %command.full_name%</info>

                Or specific packages only:

                    <info>php %command.full_name% <packages></info>
                EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $packages = $input->getArgument('packages');

        $io = new SymfonyStyle($input, $output);
        $updatedPackages = $this->importMapManager->update($packages);

        $this->renderVersionProblems($this->importMapVersionChecker, $output);

        foreach ($this->importMapManager->getLastUpdateWarnings() as $failedPackage) {
            $io->warning(\sprintf(
                'Could not update "%s": the path no longer exists in this package version. The package has been kept at its current version.'."\n"
                .'To fix this, remove it with "importmap:remove %s" and add it back with "importmap:require" using the correct path.',
                $failedPackage,
                $failedPackage,
            ));
        }

        if (0 < \count($packages)) {
            $io->success(\sprintf(
                'Updated %s package%s in importmap.php.',
                implode(', ', array_map(static fn (ImportMapEntry $entry): string => $entry->importName, $updatedPackages)),
                1 < \count($updatedPackages) ? 's' : '',
            ));
        } else {
            $io->success('Updated all packages in importmap.php.');
        }

        return Command::SUCCESS;
    }
}
