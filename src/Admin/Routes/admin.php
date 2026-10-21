<?php

use Illuminate\Support\Facades\Route;
use Webkul\BagistoApi\Admin\Http\Controllers\ApiAuditController;
use Webkul\BagistoApi\Admin\Http\Controllers\ApiSessionController;
use Webkul\BagistoApi\Admin\Http\Controllers\ApiTokenController;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

Route::prefix(config('app.admin_url'))
    ->middleware(['admin', NoCacheMiddleware::class])
    ->group(function () {
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

        Route::controller(ApiAuditController::class)
            ->prefix('settings/api-audit')
            ->group(function () {
                Route::get('', 'index')->name('admin.settings.api_audit.index');

                Route::get('view/{id}', 'view')->whereNumber('id')->name('admin.settings.api_audit.view');

                Route::post('mass-delete', 'massDestroy')->name('admin.settings.api_audit.mass_delete');

                Route::post('cleanup', 'destroyOlderThan')->name('admin.settings.api_audit.cleanup');
            });
    });
