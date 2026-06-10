<?php

use Illuminate\Support\Facades\Route;
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

                Route::get('create', 'create')->name('admin.integration.create');
                Route::post('create', 'store')->name('admin.integration.store');

                Route::get('edit/{id}', 'edit')->name('admin.integration.edit');
                Route::put('edit/{id}', 'update')->name('admin.integration.update');

                Route::post('generate/{id}', 'generate')->name('admin.integration.generate');
                Route::post('regenerate/{id}', 'regenerate')->name('admin.integration.regenerate');

                Route::delete('edit/{id}', 'destroy')->name('admin.integration.destroy');
            });

        Route::controller(AuditHistoryController::class)
            ->prefix('integration/history')
            ->group(function () {
                Route::get('', 'index')->name('admin.integration.history.index');
                Route::get('view/{id}', 'view')->name('admin.integration.history.view');
                Route::post('mass-delete', 'massDestroy')->name('admin.integration.history.mass_delete');
                Route::post('cleanup', 'destroyOlderThan')->name('admin.integration.history.cleanup');
            });
    });

/**
 * Signed, login-free revoke link delivered in token lifecycle emails.
 *
 * Uses the `signed` middleware (not `admin`) so the token owner can revoke a
 * token immediately from their inbox — even on a device where they are not
 * logged into the admin panel. The link is HMAC-signed with the app key and
 * expires after 7 days; tampered or expired links are rejected with 403.
 */
Route::prefix(config('app.admin_url'))
    ->middleware(['signed'])
    ->group(function () {
        Route::get('integration/revoke-via-email/{id}', [IntegrationController::class, 'revokeViaEmail'])
            ->name('admin.integration.revoke-via-email');
    });
