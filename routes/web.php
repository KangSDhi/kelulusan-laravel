<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController as App;

Route::get('/{any?}', [App::class, 'index'])->where('any', '^(?!api\/)[\/\w\.-]*');
