<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskController extends Controller
{
     public function index()
    {
        $data['title'] = 'Task';

        return view('admin.task.index')->with($data);
    }
}