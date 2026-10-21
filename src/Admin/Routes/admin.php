<?php

use Illuminate\Support\Facades\Route;
use Webkul\BagistoApi\Admin\Http\Controllers\ApiSessionController;
use Webkul\BagistoApi\Admin\Http\Controllers\ApiTokenController;
use Webkul\BagistoApi\Admin\Http\Controllers\AuditHistoryController;
use Webkul\BagistoApi\Admin\Http\Controllers\IntegrationController;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

Route::prefix(config('app.admin_url'))
    ->middleware(['admin', NoCacheMiddleware::class])
    ->group(function () {
        Route::controller(IntegrationController::class)
            ->prefix('integration')
            ->group(function () {
                Route::get('', 'redirectToTokens')->name('admin.integration.index');

                Route::get('token', 'index')->name('admin.integration.token.index');

                Route::get('token/create', 'create')->name('admin.integration.create');
                Route::post('token/create', 'store')->name('admin.integration.store');

                Route::get('token/edit/{id}', 'edit')->name('admin.integration.edit');
                Route::put('token/edit/{id}', 'update')->name('admin.integration.update');

                Route::post('token/generate/{id}', 'generate')->name('admin.integration.generate');
                Route::post('token/regenerate/{id}', 'regenerate')->name('admin.integration.regenerate');

                Route::delete('token/edit/{id}', 'destroy')->name('admin.integration.destroy');
            });

        Route::controller(AuditHistoryController::class)
            ->prefix('integration/history')
            ->group(function () {
                Route::get('', 'index')->name('admin.integration.history.index');
                Route::get('view/{id}', 'view')->name('admin.integration.history.view');
                Route::post('mass-delete', 'massDestroy')->name('admin.integration.history.mass_delete');
                Route::post('cleanup', 'destroyOlderThan')->name('admin.integration.history.cleanup');
            });

        Route::controller(ApiTokenController::class)
            ->prefix('settings/api-tokens')
            ->group(function () {
                Route::get('', 'index')->name('admin.settings.api_tokens.index');

                Route::get('create', 'create')->name('admin.settings.api_tokens.create');
                Route::post('create', 'store')->name('admin.settings.api_tokens.store');

                Route::get('view/{id}', 'view')->whereNumber('id')->name('admin.settings.api_tokens.view');

                Route::put('edit/{id}', 'update')->whereNumber('id')->name('admin.settings.api_tokens.update');

                Route::delete('edit/{id}', 'destroy')->whereNumber('id')->name('admin.settings.api_tokens.delete');
            });

        Route::controller(ApiSessionController::class)
            ->prefix('settings/api-sessions')
            ->group(function () {
                Route::get('', 'index')->name('admin.settings.api_sessions.index');

                Route::delete('{id}', 'destroy')->whereNumber('id')->name('admin.settings.api_sessions.delete');

                Route::post('revoke-all/{adminId}', 'revokeAll')->whereNumber('adminId')->name('admin.settings.api_sessions.revoke_all');
            });
    });

Route::prefix(config('app.admin_url'))
    ->middleware(['signed'])
    ->group(function () {
        Route::get('integration/revoke-via-email/{id}', [IntegrationController::class, 'confirmRevokeViaEmail'])
            ->name('admin.integration.revoke_via_email');

        Route::post('integration/revoke-via-email/{id}', [IntegrationController::class, 'revokeViaEmail'])
            ->name('admin.integration.revoke_via_email.perform');
    });
