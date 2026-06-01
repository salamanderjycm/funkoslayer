<?php
use Illuminate\Support\Facades\Route;

// Esta ruta atrapa CUALQUIER dirección que escribas en el navegador
// y siempre carga tu vista principal de Blade (donde está inyectado Vue).
Route::get('/{any}', function () {
    // Nota: Cambia 'welcome' por el nombre de tu archivo Blade principal 
    // (puede ser 'app', 'index', o si dejaste el por defecto, déjalo como 'welcome')
    return view('welcome'); 
})->where('any', '.*');