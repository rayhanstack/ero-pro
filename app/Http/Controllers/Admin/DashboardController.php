<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['title'] = 'Dashboard';

        return view('admin.dashboard')->with($data);
    }

    public function components()
    {
        $data['title'] = 'UI Components';
        $data['countries'] = Country::orderBy('name')->pluck('name', 'id')->toArray();

        return view('admin.components')->with($data);
    }
}
