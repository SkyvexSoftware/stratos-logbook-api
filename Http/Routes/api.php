<?php

use Modules\StratosCore\Http\Middleware\StratosAuth;
use Modules\StratosCore\Http\Middleware\StratosHeaders;
use Modules\StratosLogbook\Http\Controllers\Api\LogbookController;

Route::group(['middleware' => [StratosHeaders::class, StratosAuth::class]], function () {
    Route::match(['get', 'options'], '/pireps', [LogbookController::class, 'pireps']);
    Route::match(['get', 'options'], '/pireps/{id}', [LogbookController::class, 'pirep']);
    Route::match(['get', 'options'], '/stats', [LogbookController::class, 'stats']);
});
