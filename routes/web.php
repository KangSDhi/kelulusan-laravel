<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app', [
        'page' => 'componentsname',
        'props' => [
            'title' => 'page title',
            'constant' => ['type' => 'monthly']
        ]
    ]);
});
