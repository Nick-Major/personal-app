<?php

namespace App\Livewire\WorkRequests;

use App\Models\User;
use App\Models\WorkRequest;
use App\Models\BrigadierAssignment;
use Livewire\Component;
use Livewire\Attributes\Validate;

class AssignBrigadier extends Component
{
    public WorkRequest $workRequest;
    
    #[Validate('required|exists:users,id')]
    public $selectedBrigadier;
    
    #[Validate('required|date|after_or_equal:today')]
    public $workDate;
    
    public $search = '';
    public $availableBrigadiers = [];

    public function mount(WorkRequest $workRequest)
    {
        $this->workRequest = $workRequest;
        $this->workDate = $workRequest->work_date?->format('Y-m-d');
    }

    public function updatedSearch()
    {
        $this->availableBrigadiers = User::role('executor')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('surname', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->whereDoesntHave('brigadierAssignments', function ($query) {
                $query->where('status', 'active')
                      ->whereHas('assignment_dates', function ($q) {
                          $q->where('work_date', $this->workDate);
                      });
            })
            ->limit(10)
            ->get();
    }

    public function assignBrigadier()
    {
        $this->validate();

        // Проверяем конфликты
        $hasConflict = BrigadierAssignment::where('brigadier_id', $this->selectedBrigadier)
            ->where('status', 'active')
            ->whereHas('assignment_dates', function ($query) {
                $query->where('work_date', $this->workDate);
            })
            ->exists();

        if ($hasConflict) {
            $this->addError('selectedBrigadier', 'Выбранный бригадир уже занят на эту дату');
            return;
        }

        // Назначаем бригадира
        $this->workRequest->update([
            'brigadier_id' => $this->selectedBrigadier,
            'status' => 'pending_brigadier_confirmation'
        ]);

        // Создаем временное назначение
        $assignment = BrigadierAssignment::create([
            'brigadier_id' => $this->selectedBrigadier,
            'initiator_id' => auth()->id(),
            'status' => 'active',
            'comment' => "Временное назначение для заявки #{$this->workRequest->id}"
        ]);

        $assignment->assignment_dates()->create([
            'work_date' => $this->workDate
        ]);

        session()->flash('message', 'Бригадир успешно назначен! Ожидайте подтверждения.');
        
        $this->dispatch('brigadier-assigned');
        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.work-requests.assign-brigadier');
    }
}
