<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskListController extends Controller
{
    public function init()
    {
        return view('task_list');
    }
}
