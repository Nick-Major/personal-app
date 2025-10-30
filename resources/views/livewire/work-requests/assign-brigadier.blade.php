<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Заголовок -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Назначение бригадира</h3>
                <button wire:click="$dispatch('close-modal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Форма -->
            <div class="mt-4 space-y-4">
                <!-- Информация о заявке -->
                <div class="bg-blue-50 p-3 rounded">
                    <p class="text-sm text-blue-800">
                        <strong>Заявка:</strong> {{ $workRequest->request_number ?? 'Н/Д' }}<br>
                        <strong>Дата работы:</strong> {{ $workRequest->work_date?->format('d.m.Y') }}
                    </p>
                </div>

                <!-- Поиск бригадира -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Поиск исполнителя</label>
                    <input type="text" 
                           wire:model.live="search"
                           placeholder="Введите имя, фамилию или email..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Список доступных бригадиров -->
                @if($search && count($availableBrigadiers) > 0)
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md">
                    @foreach($availableBrigadiers as $brigadier)
                    <div class="p-3 border-b hover:bg-gray-50 cursor-pointer 
                               {{ $selectedBrigadier == $brigadier->id ? 'bg-blue-50 border-blue-200' : '' }}"
                         wire:click="selectedBrigadier = {{ $brigadier->id }}">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">{{ $brigadier->full_name }}</p>
                                <p class="text-sm text-gray-600">{{ $brigadier->email }}</p>
                                <p class="text-sm text-gray-500">{{ $brigadier->phone }}</p>
                            </div>
                            @if($selectedBrigadier == $brigadier->id)
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @elseif($search)
                <div class="text-center py-4 text-gray-500">
                    Исполнители не найдены
                </div>
                @endif

                <!-- Дата работы -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Дата работы</label>
                    <input type="date" 
                           wire:model="workDate"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('workDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                @error('selectedBrigadier') 
                <div class="bg-red-50 border border-red-200 rounded-md p-3">
                    <p class="text-red-700 text-sm">{{ $message }}</p>
                </div>
                @enderror
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-3 pt-4 mt-6 border-t">
                <button wire:click="$dispatch('close-modal')" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Отмена
                </button>
                <button wire:click="assignBrigadier" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50">
                    <span wire:loading.remove>Назначить</span>
                    <span wire:loading>Назначение...</span>
                </button>
            </div>
        </div>
    </div>
</div>
