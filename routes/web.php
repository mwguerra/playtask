<?php

use App\Livewire\LandingPage;
use App\Livewire\PublicTodoList;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPage::class)->name('home');
Route::get('/l/{slug}', PublicTodoList::class)->name('public.list');
