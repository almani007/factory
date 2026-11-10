<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'name' => config('app.name', 'Zoventra FactoryOS'),
            'version' => '1.0.0',
            'status' => 'operational',
            'tenant' => app('tenant') ? app('tenant')->getCurrentTenantId() : null,
        ]);
    }
}