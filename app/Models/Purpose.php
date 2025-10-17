<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\PayerSelectionType;

class Purpose extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'has_custom_payer_selection',
        'is_active',
        'payer_selection_type',
        'default_payer_company'
    ];

    protected $casts = [
        'has_custom_payer_selection' => 'boolean',
        'payer_selection_type' => PayerSelectionType::class,
        'is_active' => 'boolean'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payerCompanies()
    {
        return $this->hasMany(PurposePayerCompany::class);
    }

    public function addressRules()
    {
        return $this->hasMany(PurposeAddressRule::class);
    }

    public function workRequests()
    {
        return $this->hasMany(WorkRequest::class);
    }

    // Логика определения плательщика
    public function resolvePayerCompany(?Address $address = null): ?string
    {
        return match($this->payer_selection_type) {
            PayerSelectionType::STRICT => $this->default_payer_company,
            PayerSelectionType::ADDRESS_BASED => $this->getPayerByAddress($address),
            PayerSelectionType::OPTIONAL => null, // Выбирается при создании заявки
            default => $this->default_payer_company,
        };
    }

    protected function getPayerByAddress(?Address $address): ?string
    {
        if (!$address) return $this->default_payer_company;

        $rule = $this->addressRules()
            ->where('address_id', $address->id)
            ->orderBy('priority', 'desc')
            ->first();

        return $rule?->payer_company ?? $this->default_payer_company;
    }
}
