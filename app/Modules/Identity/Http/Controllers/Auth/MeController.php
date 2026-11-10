<?php

namespace App\Modules\Identity\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request)
    {
        return $request->user()->load(['roles', 'permissions', 'factories']);
    }
}