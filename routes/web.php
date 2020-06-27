<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/Plans', 'PagoController@store')->name('planes');

Route::post('Confirm/Plans', 'PagoController@ConfirmPlan')->name('confirmplans');
