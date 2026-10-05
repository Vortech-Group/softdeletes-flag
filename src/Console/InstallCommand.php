<?php

declare(strict_types=1);

namespace Vortech\SoftDeletesFlag\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Vortech\SoftDeletesFlag\Providers\SoftDeletesFlagServiceProvider;

#[AsCommand(name: 'softdeletes-flag:install', description: 'Publish the SoftDeletesFlag configuration file')]
final class InstallCommand extends Command
{
    protected $signature = 'softdeletes-flag:install {--force : Overwrite the existing configuration file}';

    public function handle(): int
    {
        $this->call(
            command: 'vendor:publish',
            arguments: [
                '--provider' => SoftDeletesFlagServiceProvider::class,
                '--tag' => 'softdeletes-config',
                '--force' => $this->option('force'),
            ]
        );

        $this->components->info('SoftDeletesFlag installed. Add $table->softDeletesFlag() to your migrations and the SoftDeletesFlag trait to your models.');

        return self::SUCCESS;
    }
}
