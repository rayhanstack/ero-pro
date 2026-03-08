<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['title'] = 'Dashboard';
        return view("admin.dashboard")->with($data);
    }

    public function components()
    {
        $data['title'] = 'UI Components';
        return view("admin.components")->with($data);
    }
}
