<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/o-nas', 'pages.about')->name('about');
Route::view('/produkty-sluzby', 'pages.products')->name('products');
Route::view('/kontakt', 'pages.contact')->name('contact');

Route::post('/kontakt', function (Request $request) {
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'message' => ['required', 'string', 'max:2000'],
    ]);

    return back()->with('status', 'Ďakujeme za správu. Ozveme sa vám čo najskôr.');
})->name('contact.submit');
