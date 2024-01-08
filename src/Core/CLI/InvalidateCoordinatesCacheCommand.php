<?php

declare(strict_types=1);

namespace App\Core\CLI;

use App\Core\Service\Geocoder\Cache\CoordinatesCacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class InvalidateCoordinatesCacheCommand extends Command
{
    protected static $defaultName = 'app:invalidate-coordinates-cache';

    public function __construct(
        private readonly CoordinatesCacheInterface $coordinatesCache,
        private readonly int $cacheMinutes = 600,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->coordinatesCache->clearOlderThanMinutes($this->cacheMinutes);

        $io->success("Invalidated coordinates cache older than $this->cacheMinutes minutes");

        return Command::SUCCESS;
    }
}
