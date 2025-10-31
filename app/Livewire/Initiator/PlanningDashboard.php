<?php

namespace App\Livewire\Initiator;

use App\Models\User;
use App\Models\BrigadierAssignment;
use App\Models\Address;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class PlanningDashboard extends Component
{
    use WithPagination;

    public $dateFilter = '';
    public $periodStart = '';
    public $periodEnd = '';
    public $brigadierFilter = '';
    
    public $availableBrigadiers = [];
    public $showAssignmentModal = false;

    protected $queryString = [
        'dateFilter' => ['except' => ''],
        'periodStart' => ['except' => ''],
        'periodEnd' => ['except' => ''],
        'brigadierFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->availableBrigadiers = User::role('executor')
            ->orderBy('name')
            ->get();
    }

    public function openAssignmentModal()
    {
        $this->showAssignmentModal = true;
    }

    public function closeAssignmentModal()
    {
        $this->showAssignmentModal = false;
    }

    protected $listeners = [
        'assignment-created' => 'handleAssignmentCreated',
        'close-assignment-modal' => 'closeAssignmentModal'
    ];

    public function handleAssignmentCreated()
    {
        $this->showAssignmentModal = false;
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedPeriodStart()
    {
        $this->resetPage();
    }

    public function updatedPeriodEnd()
    {
        $this->resetPage();
    }

    public function getAssignmentsProperty()
    {
        return BrigadierAssignment::with(['brigadier', 'initiator', 'assignment_dates', 'plannedAddress'])
            ->when($this->dateFilter, function ($query) {
                $query->whereHas('assignment_dates', function ($q) {
                    $q->whereDate('assignment_date', $this->dateFilter); // ✅ ИСПРАВЛЕНО: assignment_date
                });
            })
            ->when($this->periodStart && $this->periodEnd, function ($query) {
                $query->whereHas('assignment_dates', function ($q) {
                    $q->whereBetween('assignment_date', [ // ✅ ИСПРАВЛЕНО: assignment_date
                        $this->periodStart,
                        $this->periodEnd
                    ]);
                });
            })
            ->when($this->brigadierFilter, function ($query) {
                $query->where('brigadier_id', $this->brigadierFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getGroupedAssignmentsProperty()
    {
        $grouped = [];
        
        foreach ($this->assignments as $assignment) {
            $key = $assignment->initiator_id . '_' . $assignment->brigadier_id;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'initiator' => $assignment->initiator,
                    'brigadier' => $assignment->brigadier,
                    'assignments' => [],
                ];
            }
            
            $grouped[$key]['assignments'][] = $assignment;
        }
        
        return $grouped;
    }

    public function getAddressDisplay($assignment)
    {
        if ($assignment->is_custom_planned_address) { // ✅ ИСПРАВЛЕНО: is_custom_planned_address
            return $assignment->planned_custom_address; // ✅ ИСПРАВЛЕНО: planned_custom_address
        }
        
        return $assignment->plannedAddress?->full_address ?? 'Адрес не указан'; // ✅ ИСПРАВЛЕНО: plannedAddress
    }

    public function getBrigadierScheduleProperty()
    {
        $schedule = [];
        
        $assignments = BrigadierAssignment::with(['brigadier', 'initiator', 'assignment_dates'])
            ->whereHas('assignment_dates')
            ->get();

        foreach ($assignments as $assignment) {
            foreach ($assignment->assignment_dates as $date) {
                $dateKey = $date->assignment_date->format('Y-m-d'); // ✅ ИСПРАВЛЕНО: assignment_date
                $brigadierId = $assignment->brigadier_id;
                
                if (!isset($schedule[$dateKey])) {
                    $schedule[$dateKey] = [];
                }
                
                if (!isset($schedule[$dateKey][$brigadierId])) {
                    $schedule[$dateKey][$brigadierId] = [];
                }
                
                $schedule[$dateKey][$brigadierId][] = [
                    'assignment' => $assignment,
                    'initiator' => $assignment->initiator->name,
                    'status' => $date->status, // ✅ ИСПРАВЛЕНО: берем статус из dates
                    'status_display' => $this->getStatusDisplay($date->status), // ✅ ИСПРАВЛЕНО: берем статус из dates
                ];
            }
        }

        return $schedule;
    }

    private function getStatusDisplay($status)
    {
        return match($status) {
            'confirmed' => ['text' => 'Подтверждено', 'color' => 'green'], // ✅ ИСПРАВЛЕНО: confirmed
            'pending' => ['text' => 'Ожидает ответа', 'color' => 'yellow'],
            'rejected' => ['text' => 'Отклонено', 'color' => 'red'], // ✅ ИСПРАВЛЕНО: rejected
            default => ['text' => 'Неизвестно', 'color' => 'gray'],
        };
    }

    public function cancelAssignment($assignmentId)
    {
        $assignment = BrigadierAssignment::findOrFail($assignmentId);
        
        if ($assignment->initiator_id !== auth()->id()) {
            session()->flash('error', 'Вы можете отменять только свои назначения');
            return;
        }

        $assignment->delete();
        session()->flash('message', 'Назначение отменено');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.initiator.planning-dashboard', [
            'assignments' => $this->assignments,
            'groupedAssignments' => $this->groupedAssignments,
            'brigadierSchedule' => $this->brigadierSchedule,
        ]);
    }
}
