<?php

defined('ABSPATH') or die;

use NinjaPhotoFeed\App\Http\Controllers\Platforms\Feeds\ConfigsController as FeedsConfigsController;
use NinjaPhotoFeed\App\Http\Controllers\Platforms\Feeds\MetaController as FeedsMetaController;
use NinjaPhotoFeed\App\Http\Controllers\Platforms\PlatformController;
use NinjaPhotoFeed\App\Http\Controllers\SettingsController;
use NinjaPhotoFeed\App\Http\Controllers\ShoppablesController;
use NinjaPhotoFeed\App\Http\Controllers\TemplatesController;

/**
 * Ninja Photo Feed - Instagram Routes
 *
 * @var $router NinjaPhotoFeed\Framework\Http\Router
 */

$router->prefix('platforms')->withPolicy('PlatformPolicy')->group(function ($router) {
    $router->get('/', [PlatformController::class, 'index']);
    $router->get('/enabled', [PlatformController::class, 'enabledPlatforms']);
    $router->get('/get-statuses', [PlatformController::class, 'getStatuses']);
    $router->post('/update-statuses', [PlatformController::class, 'updateStatuses']);

    $router->prefix('feeds')->group(function ($router) {
        $router->get('/configs', [FeedsConfigsController::class, 'index']);
        $router->post('/configs', [FeedsConfigsController::class, 'store']);
        $router->post('/configs/prepare', [FeedsConfigsController::class, 'prepare']);
        $router->post('/configs/consume', [FeedsConfigsController::class, 'consume']);
        $router->delete('/configs', [FeedsConfigsController::class, 'delete']);
    });
});

$router->prefix('settings')->withPolicy('SettingsPolicy')->group(function ($router) {
    $router->get('/', [SettingsController::class, 'index']);
    $router->put('/', [SettingsController::class, 'update']);
    $router->delete('/', [SettingsController::class, 'delete']);

    $router->get('/advance-settings', [SettingsController::class, 'getAdvanceSettings']);
    $router->post('/advance-settings', [SettingsController::class, 'saveAdvanceSettings']);

    $router->delete('/reset-images', [SettingsController::class, 'resetData']);
    $router->delete('/reset-error-log', [SettingsController::class, 'resetErrorLog']);
    $router->delete('/delete-all-data', [SettingsController::class, 'deleteAllData']);
});

$router->prefix('shoppable')->withPolicy('ShoppablePolicy')->group(function ($router) {
    $router->get('/posts', [ShoppablesController::class, 'getPosts']);
    $router->get('/', [ShoppablesController::class, 'index']);
    $router->put('/', [ShoppablesController::class, 'update']);
    $router->delete('/', [ShoppablesController::class, 'delete']);
    $router->put('/template-settings/{id}', [ShoppablesController::class, 'storeTemplateSettings']);
});

$router->prefix('templates')->withPolicy('TemplatePolicy')->group(function ($router) {
    $router->get('/', [TemplatesController::class, 'index']);
    $router->post('/', [TemplatesController::class, 'create']);
    $router->post('/duplicate', [TemplatesController::class, 'duplicate']);
    $router->delete('/', [TemplatesController::class, 'delete']);

    $router->put('/title/{id}', [TemplatesController::class, 'updateTitle'])->int('id');

    $router->prefix('meta')->group(function ($router) {
        $router->prefix('feeds')->group(function ($router) {
            $router->get('/{id}', [FeedsMetaController::class, 'index'])->int('id');
            $router->put('/{id}', [FeedsMetaController::class, 'update'])->int('id');
            $router->post('/{id}/edit', [FeedsMetaController::class, 'edit'])->int('id');
        });
    });
});
