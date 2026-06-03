<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ExpiryController extends Controller
{
    public function index()
    {

        return view('expiry.index');
    }
}
