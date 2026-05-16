<?php

use \Modules\StratosLogbook\Http\Controllers\Api\LogbookController;
use \Modules\StratosCore\Http\Middleware\StratosAuth;
use \Modules\StratosCore\Http\Middleware\StratosHeaders;

Route::group(['middleware' => [StratosHeaders::class, StratosAuth::class]], function () {
    Route::match(['get', 'options'], '/pireps', [LogbookController::class, 'pireps']);
    Route::match(['get', 'options'], '/pireps/{id}', [LogbookController::class, 'pirep']);
    Route::match(['get', 'options'], '/stats', [LogbookController::class, 'stats']);
});
