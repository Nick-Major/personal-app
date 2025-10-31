<?php

namespace App\Livewire\WorkRequests;

use App\Models\User;
use App\Models\BrigadierAssignment;
use App\Models\Address;
use Livewire\Component;
use Livewire\Attributes\Validate;

class AssignBrigadier extends Component
{
    #[Validate('required|exists:users,id')]
    public $selectedBrigadier;

    #[Validate('required|date|after_or_equal:today')]
    public $workDate;

    #[Validate('required')]
    public $plannedAddressType = 'existing';

    public $existingAddressId;
    public $customAddress = '';
    
    #[Validate('required')]
    public $comment = '';

    public $search = '';
    public $availableBrigadiers = [];
    public $availableAddresses = [];

    public function mount()
    {
        $this->resetState();
        $this->availableAddresses = Address::orderBy('short_name')->get();
        $this->loadAllExecutors();
    }

    public function resetState()
    {
        $this->selectedBrigadier = null;
        $this->workDate = '';
        $this->plannedAddressType = 'existing';
        $this->existingAddressId = null;
        $this->customAddress = '';
        $this->comment = '';
        $this->search = '';
        $this->availableBrigadiers = [];
        $this->resetErrorBag();
    }

    public function loadAllExecutors()
    {
        $this->availableBrigadiers = User::role('executor')
            ->orderBy('name')
            ->orderBy('surname')
            ->get();
    }

    public function updatedSearch()
    {
        if (empty($this->search)) {
            $this->loadAllExecutors();
            return;
        }

        $this->availableBrigadiers = User::role('executor')
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('surname', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->orderBy('surname')
            ->get();
    }

    public function updatedWorkDate()
    {
        if ($this->workDate && $this->selectedBrigadier) {
            $this->checkConflictForSelectedBrigadier();
        }
    }

    public function updatedSelectedBrigadier()
    {
        if ($this->workDate && $this->selectedBrigadier) {
            $this->checkConflictForSelectedBrigadier();
        }
    }

    public function updatedPlannedAddressType()
    {
        // Сбрасываем поля адреса при смене типа
        $this->existingAddressId = null;
        $this->customAddress = '';
        $this->resetErrorBag(['existingAddressId', 'customAddress']);
    }

    private function checkConflictForSelectedBrigadier()
    {
        if (!$this->workDate || !$this->selectedBrigadier) {
            return;
        }

        $hasConflict = BrigadierAssignment::where('brigadier_id', $this->selectedBrigadier)
            ->whereHas('assignment_dates', function ($query) {
                $query->where('assignment_date', $this->workDate)
                    ->where('status', 'confirmed'); // ✅ проверяем статус в dates
            })
            ->exists();

        if ($hasConflict) {
            $this->addError('selectedBrigadier', 'ВНИМАНИЕ: Выбранный бригадир уже подтвердил работу на эту дату у другого инициатора. Вы все равно можете отправить запрос, но он, скорее всего, будет отклонен.');
        } else {
            $this->resetErrorBag('selectedBrigadier');
        }
    }

    public function assignBrigadier()
    {
        $this->validate();

        // Дополнительная валидация для адреса
        if ($this->plannedAddressType === 'existing' && !$this->existingAddressId) {
            $this->addError('existingAddressId', 'Выберите адрес из списка');
            return;
        }

        if ($this->plannedAddressType === 'custom' && empty($this->customAddress)) {
            $this->addError('customAddress', 'Введите адрес');
            return;
        }

        // Финальная проверка конфликтов
        if ($this->workDate) {
            $hasConflict = BrigadierAssignment::where('brigadier_id', $this->selectedBrigadier)
                ->whereHas('assignment_dates', function ($query) {
                    $query->where('assignment_date', $this->workDate)
                        ->where('status', 'confirmed'); // ✅ проверяем статус в dates
                })
                ->exists();

            if ($hasConflict) {
                $this->addError('selectedBrigadier', 'Выбранный бригадир уже подтвердил работу на эту дату у другого инициатора. Выберите другого бригадира или другую дату.');
                return;
            }
        }

        // Создаем назначение
        $assignment = BrigadierAssignment::create([
            'brigadier_id' => $this->selectedBrigadier,
            'initiator_id' => auth()->id(),
            'comment' => $this->comment,
            'planned_address_id' => $this->plannedAddressType === 'existing' ? $this->existingAddressId : null,
            'planned_custom_address' => $this->plannedAddressType === 'custom' ? $this->customAddress : null,
            'is_custom_planned_address' => $this->plannedAddressType === 'custom',
        ]);

        $assignment->assignment_dates()->create([
            'assignment_date' => $this->workDate,
            'status' => 'pending',
        ]);

        session()->flash('message', 'Запрос бригадиру отправлен! Ожидайте подтверждения.');

        $this->dispatch('assignment-created');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->resetState();
        $this->dispatch('close-assignment-modal');
    }

    public function render()
    {
        return view('livewire.work-requests.assign-brigadier');
    }
}
