<?php

$src = __DIR__.'/../../src';

it('every module has the expected DDD directory structure', function () use ($src) {
    $modules = array_filter(
        glob($src.'/*', GLOB_ONLYDIR),
        fn ($dir) => is_dir($dir.'/Infrastructure/Persistence')
    );

    expect($modules)->not->toBeEmpty();

    foreach ($modules as $module) {
        $name = basename($module);

        expect(is_dir($module.'/Application'))->toBeTrue()
            ->and($name.' module should have an Application layer');
        expect(is_dir($module.'/Domain'))->toBeTrue()
            ->and($name.' module should have a Domain layer');
        expect(is_dir($module.'/Infrastructure/Persistence'))->toBeTrue()
            ->and($name.' module should have an Infrastructure persistence layer');
        expect(is_dir($module.'/Presentation'))->toBeTrue()
            ->and($name.' module should have a Presentation layer');
    }
});

it('shared contracts define the posting service interfaces', function () use ($src) {
    expect(file_exists($src.'/Shared/Contracts/JournalEntryServiceInterface.php'))
        ->toBeTrue();
    expect(file_exists($src.'/Shared/Contracts/StockMovementServiceInterface.php'))
        ->toBeTrue();
});

it('shared exceptions live under Shared\\Exceptions', function () use ($src) {
    $files = glob($src.'/Shared/Exceptions/*.php');

    expect($files)->not->toBeEmpty();
    foreach ($files as $file) {
        expect(str_contains(file_get_contents($file), 'namespace Shared\\Exceptions'))
            ->toBeTrue(basename($file).' should declare the Shared\\Exceptions namespace');
    }
});
