<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" 
     x-data="{ open: true }" 
     x-show="open"
     @close-assignment-modal.window="open = false">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Заголовок -->
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Назначение бригадира</h3>
                <button @click="$dispatch('close-assignment-modal')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Форма -->
            <div class="mt-4 space-y-4">
                <!-- Поиск бригадира -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Поиск исполнителя</label>
                    <input type="text" 
                           wire:model.live="search"
                           placeholder="Введите ФИО или email для поиска..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Показано: {{ count($availableBrigadiers) }} исполнителей</p>
                </div>

                <!-- Список доступных бригадиров -->
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md">
                    @if(count($availableBrigadiers) > 0)
                        @foreach($availableBrigadiers as $brigadier)
                        <div class="p-3 border-b hover:bg-gray-50 cursor-pointer 
                                   {{ $selectedBrigadier == $brigadier->id ? 'bg-blue-50 border-blue-200' : '' }}"
                             wire:click="$set('selectedBrigadier', {{ $brigadier->id }})">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium">{{ $brigadier->full_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $brigadier->email }}</p>
                                    <p class="text-sm text-gray-500">{{ $brigadier->phone ?? 'Телефон не указан' }}</p>
                                </div>
                                @if($selectedBrigadier == $brigadier->id)
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-gray-500">
                            Исполнители не найдены
                        </div>
                    @endif
                </div>

                @if($selectedBrigadier)
                <div class="bg-green-50 border border-green-200 rounded-md p-3">
                    <p class="text-green-700 text-sm">
                        ✅ Выбран: {{ $availableBrigadiers->firstWhere('id', $selectedBrigadier)?->full_name }}
                    </p>
                </div>
                @endif

                <!-- Дата работы -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Дата смены *</label>
                    <input type="date" 
                           wire:model="workDate"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    @error('workDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Выбор адреса -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Адрес работ *</label>
                    <div class="space-y-2">
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="plannedAddressType" value="existing" class="mr-2">
                                <span class="text-sm">Выбрать из списка</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="plannedAddressType" value="custom" class="mr-2">
                                <span class="text-sm">Ввести вручную</span>
                            </label>
                        </div>

                        @if($plannedAddressType === 'existing')
                        <div>
                            <select wire:model="existingAddressId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Выберите адрес...</option>
                                @foreach($availableAddresses as $address)
                                    <option value="{{ $address->id }}">{{ $address->short_name }} - {{ $address->full_address }}</option>
                                @endforeach
                            </select>
                            @error('existingAddressId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        @if($plannedAddressType === 'custom')
                        <div>
                            <textarea wire:model="customAddress"
                                      placeholder="Введите полный адрес работ..."
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                            @error('customAddress') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Комментарий -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий для исполнителя *</label>
                    <textarea wire:model="comment"
                              placeholder="Опишите задачи, особенности работы и т.д."
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    @error('comment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                @error('selectedBrigadier') 
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                    <p class="text-yellow-700 text-sm">{{ $message }}</p>
                </div>
                @enderror
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-3 pt-4 mt-6 border-t">
                <button wire:click="closeModal" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Отмена
                </button>
                <button wire:click="assignBrigadier" 
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50">
                    <span wire:loading.remove>Отправить запрос</span>
                    <span wire:loading>Отправка...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js для управления модальным окном -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
