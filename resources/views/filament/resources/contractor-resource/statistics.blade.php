<div class="p-6 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-4 shadow border">
            <div class="text-2xl font-bold text-gray-900">{{ $contractor->getTotalExecutorsCount() }}</div>
            <div class="text-sm text-gray-600">Исполнителей</div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow border">
            <div class="text-2xl font-bold text-green-600">{{ $contractor->getActiveShiftsCount() }}</div>
            <div class="text-sm text-gray-600">Активных смен</div>
        </div>
        <div class="bg-white rounded-lg p-4 shadow border">
            <div class="text-2xl font-bold text-blue-600">{{ $contractor->getCompletedShiftsThisMonth() }}</div>
            <div class="text-sm text-gray-600">Смен за месяц</div>
        </div>
    </div>

    @if($contractor->executors->count() > 0)
    <div class="bg-white rounded-lg p-4 shadow border">
        <h3 class="text-lg font-medium mb-3">Исполнители</h3>
        <div class="space-y-2">
            @foreach($contractor->executors as $executor)
            <div class="flex justify-between items-center py-2 border-b">
                <div>
                    <div class="font-medium">{{ $executor->full_name }}</div>
                    <div class="text-sm text-gray-600">{{ $executor->email }}</div>
                </div>
                <div class="text-sm text-gray-500">
                    {{ $executor->shifts()->count() }} смен
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>