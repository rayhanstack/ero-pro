<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
     public function index()
    {
        $data['title'] = 'Client';

        return view('admin.client.index')->with($data);
    }
}
