<?php

namespace App\Services;

use App\Models\WorkRequest;
use App\Models\PayerRule;

class PayerDeterminationService
{
    public function determinePayer(WorkRequest $workRequest)
    {
        // Если плательщик определяется индивидуально - возвращаем null
        if ($this->isCustomPayer($workRequest)) {
            return null;
        }

        // Ищем правила по приоритету
        $rules = PayerRule::where(function($query) use ($workRequest) {
            $query->where('purpose_id', $workRequest->purpose_id)
                  ->where(function($q) use ($workRequest) {
                      $q->where('address_id', $workRequest->address_id)
                        ->orWhere('address_program_id', $workRequest->project->addressPrograms->where('address_id', $workRequest->address_id)->first()?->id)
                        ->orWhere('project_id', $workRequest->project_id);
                  });
        })
        ->orderBy('priority', 'asc')
        ->get();

        // Возвращаем первое подходящее правило
        return $rules->first()?->payer_company;
    }

    private function isCustomPayer(WorkRequest $workRequest)
    {
        // Проверяем, есть ли правило с is_custom = true для этой комбинации
        return PayerRule::where('purpose_id', $workRequest->purpose_id)
            ->where('address_id', $workRequest->address_id)
            ->where('is_custom', true)
            ->exists();
    }
}
