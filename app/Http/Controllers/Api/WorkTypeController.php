<?php
// app/Http/Controllers/Api/WorkTypeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkType;
use Illuminate\Http\Request;

class WorkTypeController extends Controller
{
    public function index()
    {
        $workTypes = WorkType::all();
        return response()->json($workTypes);
    }
}
