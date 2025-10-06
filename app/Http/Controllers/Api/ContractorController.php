<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractorResource;
use App\Models\Contractor;
use Illuminate\Http\Request;

class ContractorController extends Controller
{
    public function index()
    {
        $contractors = Contractor::all();
        return ContractorResource::collection($contractors);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email',
            'specializations' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $contractor = Contractor::create($validated);
        return new ContractorResource($contractor);
    }

    public function show(Contractor $contractor)
    {
        return new ContractorResource($contractor);
    }

    public function update(Request $request, Contractor $contractor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'specializations' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $contractor->update($validated);
        return new ContractorResource($contractor);
    }

    public function destroy(Contractor $contractor)
    {
        $contractor->delete();
        return response()->json(null, 204);
    }

    public function active()
    {
        $contractors = Contractor::where('is_active', true)->get();
        return ContractorResource::collection($contractors);
    }
}
