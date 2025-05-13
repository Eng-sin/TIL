<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskListController;

Route::get('/', [TaskListController::class, 'init']);
