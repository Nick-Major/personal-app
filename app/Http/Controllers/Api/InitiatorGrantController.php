<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InitiatorGrantResource;
use App\Models\InitiatorGrant;
use Illuminate\Http\Request;

class InitiatorGrantController extends Controller
{
    public function index()
    {
        $grants = InitiatorGrant::with(['initiator', 'brigadier'])->get();
        return InitiatorGrantResource::collection($grants);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'initiator_id' => 'required|exists:users,id',
            'brigadier_id' => 'required|exists:users,id',
            'is_temporary' => 'sometimes|boolean',
            'expires_at' => 'nullable|date|required_if:is_temporary,true',
            'is_active' => 'sometimes|boolean',
        ]);

        $grant = InitiatorGrant::create($validated);
        return new InitiatorGrantResource($grant->load(['initiator', 'brigadier']));
    }

    public function show(InitiatorGrant $initiatorGrant)
    {
        return new InitiatorGrantResource($initiatorGrant->load(['initiator', 'brigadier']));
    }

    public function update(Request $request, InitiatorGrant $initiatorGrant)
    {
        $validated = $request->validate([
            'initiator_id' => 'sometimes|exists:users,id',
            'brigadier_id' => 'sometimes|exists:users,id',
            'is_temporary' => 'sometimes|boolean',
            'expires_at' => 'nullable|date|required_if:is_temporary,true',
            'is_active' => 'sometimes|boolean',
        ]);

        $initiatorGrant->update($validated);
        return new InitiatorGrantResource($initiatorGrant->load(['initiator', 'brigadier']));
    }

    public function destroy(InitiatorGrant $initiatorGrant)
    {
        $initiatorGrant->delete();
        return response()->json(null, 204);
    }

    public function active()
    {
        $grants = InitiatorGrant::where('is_active', true)
            ->with(['initiator', 'brigadier'])
            ->get();
        return InitiatorGrantResource::collection($grants);
    }

    public function deactivate(InitiatorGrant $initiatorGrant)
    {
        $initiatorGrant->update(['is_active' => false]);
        return new InitiatorGrantResource($initiatorGrant->load(['initiator', 'brigadier']));
    }

    public function activate(InitiatorGrant $initiatorGrant)
    {
        $initiatorGrant->update(['is_active' => true]);
        return new InitiatorGrantResource($initiatorGrant->load(['initiator', 'brigadier']));
    }
}
