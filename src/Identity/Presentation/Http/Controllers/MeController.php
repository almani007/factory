<?php

namespace Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request)
    {
        return $request->user()->load(['roles', 'permissions', 'factories']);
    }
}