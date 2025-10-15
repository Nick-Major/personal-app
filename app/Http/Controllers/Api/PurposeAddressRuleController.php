<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurposeAddressRuleResource;
use App\Models\PurposeAddressRule;
use Illuminate\Http\Request;

class PurposeAddressRuleController extends Controller
{
    public function index()
    {
        $rules = PurposeAddressRule::with(['purpose', 'address'])->get();
        return PurposeAddressRuleResource::collection($rules);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose_id' => 'required|exists:purposes,id',
            'address_id' => 'nullable|exists:addresses,id',
            'payer_company' => 'required|string|max:255',
            'priority' => 'integer|min:1'
        ]);

        $rule = PurposeAddressRule::create($validated);
        return new PurposeAddressRuleResource($rule->load(['purpose', 'address']));
    }

    public function show(PurposeAddressRule $purposeAddressRule)
    {
        return new PurposeAddressRuleResource($purposeAddressRule->load(['purpose', 'address']));
    }

    public function update(Request $request, PurposeAddressRule $purposeAddressRule)
    {
        $validated = $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'payer_company' => 'sometimes|string|max:255',
            'priority' => 'sometimes|integer|min:1'
        ]);

        $purposeAddressRule->update($validated);
        return new PurposeAddressRuleResource($purposeAddressRule->load(['purpose', 'address']));
    }

    public function destroy(PurposeAddressRule $purposeAddressRule)
    {
        $purposeAddressRule->delete();
        return response()->json(null, 204);
    }

    public function byPurposeAndAddress($purposeId, $addressId = null)
    {
        $query = PurposeAddressRule::where('purpose_id', $purposeId)
            ->with(['purpose', 'address']);

        if ($addressId) {
            $query->where('address_id', $addressId);
        } else {
            $query->whereNull('address_id');
        }

        return PurposeAddressRuleResource::collection($query->get());
    }
}
