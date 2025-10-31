<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Заголовок и кнопка -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Планирование бригадиров</h1>
                    <p class="text-gray-600 mt-2">Назначение исполнителей на роль бригадира для будущих смен</p>
                </div>
                <button wire:click="openAssignmentModal" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    + Назначить бригадира
                </button>
            </div>

            <!-- Фильтры -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <!-- Фильтр по дате -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Конкретная дата</label>
                            <input type="date" 
                                   wire:model.live="dateFilter"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Фильтр по периоду -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Начало периода</label>
                            <input type="date" 
                                   wire:model.live="periodStart"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Конец периода</label>
                            <input type="date" 
                                   wire:model.live="periodEnd"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Фильтр по бригадиру -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Бригадир</label>
                            <select wire:model.live="brigadierFilter" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Все бригадиры</option>
                                @foreach($availableBrigadiers as $brigadier)
                                    <option value="{{ $brigadier->id }}">{{ $brigadier->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Таблица назначений -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Инициатор
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Бригадир
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Даты смен
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Статус
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($groupedAssignments as $group)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $group['initiator']->full_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $group['brigadier']->full_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="space-y-1">
                                        @foreach($group['assignments'] as $assignment)
                                            @foreach($assignment->assignment_dates as $date)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $date->assignment_date->format('d.m.Y') }}
                                            </div>
                                            @endforeach
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @foreach($group['assignments'] as $assignment)
                                        @php
                                            $status = $this->getStatusDisplay($assignment->status);
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $status['color'] }}-100 text-{{ $status['color'] }}-800">
                                            {{ $status['text'] }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if(auth()->id() === $group['initiator']->id)
                                        @foreach($group['assignments'] as $assignment)
                                            @if($assignment->status === 'pending')
                                            <button wire:click="cancelAssignment({{ $assignment->id }})" 
                                                    class="text-red-600 hover:text-red-900 mr-3">
                                                Отменить
                                            </button>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-gray-500">Только для автора</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Нет назначений бригадиров
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                @if($assignments->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $assignments->links() }}
                </div>
                @endif
            </div>

            <!-- Расписание бригадиров -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Расписание бригадиров по датам
                    </h3>
                    
                    @if(count($brigadierSchedule) > 0)
                        <div class="space-y-4">
                            @foreach($brigadierSchedule as $date => $brigadiers)
                            <div class="border border-gray-200 rounded-lg">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                    <h4 class="font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($date)->format('d.m.Y') }}
                                        ({{ \Carbon\Carbon::parse($date)->translatedFormat('l') }})
                                    </h4>
                                </div>
                                <div class="p-4">
                                    @foreach($brigadiers as $brigadierId => $assignments)
                                        @php
                                            $brigadier = $availableBrigadiers->firstWhere('id', $brigadierId);
                                        @endphp
                                        @if($brigadier)
                                        <div class="mb-3 last:mb-0">
                                            <div class="font-medium text-gray-900 mb-2">
                                                {{ $brigadier->full_name }}
                                            </div>
                                            <div class="space-y-1 ml-4">
                                                @foreach($assignments as $assignment)
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-{{ $assignment['status_display']['color'] }}-600">
                                                        {{ $assignment['initiator'] }}
                                                        <span class="text-xs">({{ $assignment['status_display']['text'] }})</span>
                                                    </span>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Нет данных о расписании бригадиров</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно назначения -->
    @if($showAssignmentModal)
        @livewire('work-requests.assign-brigadier')
    @endif
</div>
