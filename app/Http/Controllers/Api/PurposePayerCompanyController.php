<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurposePayerCompanyResource;
use App\Models\PurposePayerCompany;
use Illuminate\Http\Request;

class PurposePayerCompanyController extends Controller
{
    public function index()
    {
        $payerCompanies = PurposePayerCompany::with('purpose')->get();
        return PurposePayerCompanyResource::collection($payerCompanies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purpose_id' => 'required|exists:purposes,id',
            'payer_company' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer|min:1'
        ]);

        $payerCompany = PurposePayerCompany::create($validated);
        return new PurposePayerCompanyResource($payerCompany->load('purpose'));
    }

    public function show(PurposePayerCompany $purposePayerCompany)
    {
        return new PurposePayerCompanyResource($purposePayerCompany->load('purpose'));
    }

    public function update(Request $request, PurposePayerCompany $purposePayerCompany)
    {
        $validated = $request->validate([
            'payer_company' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order' => 'sometimes|integer|min:1'
        ]);

        $purposePayerCompany->update($validated);
        return new PurposePayerCompanyResource($purposePayerCompany->load('purpose'));
    }

    public function destroy(PurposePayerCompany $purposePayerCompany)
    {
        $purposePayerCompany->delete();
        return response()->json(null, 204);
    }

    public function byPurpose($purposeId)
    {
        $payerCompanies = PurposePayerCompany::where('purpose_id', $purposeId)
            ->with('purpose')
            ->get();
        return PurposePayerCompanyResource::collection($payerCompanies);
    }
}
