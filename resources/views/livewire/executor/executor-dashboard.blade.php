<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Заголовок -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Личный кабинет исполнителя</h1>
                <p class="text-gray-600 mt-2">Управление вашими назначениями на роль бригадира</p>
            </div>

            <!-- Ожидающие подтверждения -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        Запросы на назначение
                        @if($pendingAssignments->total())
                            <span class="text-sm text-gray-500">({{ $pendingAssignments->total() }})</span>
                        @endif
                    </h2>

                    @if($pendingAssignments->count() > 0)
                        <div class="space-y-4">
                            @foreach($pendingAssignments as $assignment)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-medium text-gray-900">
                                            Запрос от: {{ $assignment->initiator->full_name }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ $assignment->comment }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                        Ожидает подтверждения
                                    </span>
                                </div>

                                <!-- Даты назначения -->
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Даты работы:</h4>
                                    <div class="space-y-1">
                                        @foreach($assignment->assignment_dates as $date)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $date->assignment_date->format('d.m.Y') }}
                                            ({{ $date->assignment_date->translatedFormat('l') }})
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Кнопки действий -->
                                <div class="flex space-x-3">
                                    <button wire:click="confirmAssignment({{ $assignment->id }})" 
                                            wire:loading.attr="disabled"
                                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 disabled:opacity-50">
                                        <span wire:loading.remove>Подтвердить</span>
                                        <span wire:loading>Подтверждение...</span>
                                    </button>
                                    <button wire:click="rejectAssignment({{ $assignment->id }})" 
                                            wire:loading.attr="disabled"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                                        <span wire:loading.remove>Отклонить</span>
                                        <span wire:loading>Отклонение...</span>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Пагинация -->
                        @if($pendingAssignments->hasPages())
                        <div class="mt-4">
                            {{ $pendingAssignments->links() }}
                        </div>
                        @endif
                    @else
                        <p class="text-gray-500 text-center py-4">Нет запросов на назначение</p>
                    @endif
                </div>
            </div>

            <!-- Подтвержденные назначения -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        Подтвержденные назначения
                        @if($confirmedAssignments->total())
                            <span class="text-sm text-gray-500">({{ $confirmedAssignments->total() }})</span>
                        @endif
                    </h2>

                    @if($confirmedAssignments->count() > 0)
                        <div class="space-y-4">
                            @foreach($confirmedAssignments as $assignment)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-medium text-gray-900">
                                            Назначение от: {{ $assignment->initiator->full_name }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ $assignment->comment }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        Подтверждено
                                    </span>
                                </div>

                                <!-- Даты назначения -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Даты работы:</h4>
                                    <div class="space-y-1">
                                        @foreach($assignment->assignment_dates as $date)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $date->assignment_date->format('d.m.Y') }}
                                            ({{ $date->assignment_date->translatedFormat('l') }})
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Пагинация -->
                        @if($confirmedAssignments->hasPages())
                        <div class="mt-4">
                            {{ $confirmedAssignments->links() }}
                        </div>
                        @endif
                    @else
                        <p class="text-gray-500 text-center py-4">Нет подтвержденных назначений</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
