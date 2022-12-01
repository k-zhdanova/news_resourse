<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * @group Modules
 *
 * API для работы с разделами админ панели
 */

class ModuleController extends Controller
{
    
    /**
     * List of modules
     *
     * @authenticated
     * @param  Request  $request
     * @return Response
     */

    public function index(Request $request)
    {
        $module  = new Module();
        $modules = $module->getAll($request);
        return response()->json($modules);
    }
    
}
