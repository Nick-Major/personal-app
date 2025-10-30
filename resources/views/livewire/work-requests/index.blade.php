<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Мои заявки') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Список заявок</h3>
                        <a href="{{ route('work-requests.create') }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Создать заявку
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        № Заявки
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Дата работы
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Бригадир
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
                                @foreach($workRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $request->request_number ?? 'Н/Д' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $request->work_date?->format('d.m.Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($request->brigadier)
                                            {{ $request->brigadier->full_name }}
                                        @else
                                            <span class="text-red-500">Не назначен</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $request->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(!$request->brigadier)
                                        <button wire:click="$dispatch('openModal', { component: 'work-requests.assign-brigadier', arguments: { workRequest: {{ $request->id }} }})"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Назначить бригадира
                                        </button>
                                        @endif
                                        <a href="#" class="text-gray-600 hover:text-gray-900">Просмотр</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $workRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
