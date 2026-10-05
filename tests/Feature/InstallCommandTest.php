<?php

declare(strict_types=1);

beforeEach(function () {
    $this->configPath = config_path('softdeletes-flag.php');

    @unlink($this->configPath);
});

afterEach(function () {
    @unlink($this->configPath);
});

it('publishes the config file', function () {
    $this->artisan('softdeletes-flag:install')->assertSuccessful();

    expect($this->configPath)->toBeFile();
});

it('does not overwrite an existing config without --force', function () {
    file_put_contents($this->configPath, '<?php return [];');

    $this->artisan('softdeletes-flag:install')->assertSuccessful();

    expect(file_get_contents($this->configPath))->toBe('<?php return [];');
});

it('overwrites an existing config with --force', function () {
    file_put_contents($this->configPath, '<?php return [];');

    $this->artisan('softdeletes-flag:install', ['--force' => true])->assertSuccessful();

    expect(file_get_contents($this->configPath))->toContain('column_name');
});
