<div>
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Дашборд системы управления персоналом</h1>
    
    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow border">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Пользователи</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['users_count'] }}</p>
            <p class="text-gray-500 text-sm mt-2">Всего в системе</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow border">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Заявки</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['work_requests_count'] }}</p>
            <a href="/work-requests" class="text-blue-500 hover:text-blue-700 text-sm mt-2 inline-block">Управление →</a>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow border">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Категории</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['categories_count'] }}</p>
            <p class="text-gray-500 text-sm mt-2">Специальностей</p>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow border">
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Подрядчики</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $stats['contractors_count'] }}</p>
            <p class="text-gray-500 text-sm mt-2">Внешние компании</p>
        </div>
    </div>
    
    <!-- Быстрые действия -->
    <div class="bg-white p-6 rounded-lg shadow border">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Быстрые действия</h2>
        <div class="flex flex-wrap gap-4">
            <a href="/work-requests/create" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition font-medium">
                📋 Создать заявку
            </a>
            <a href="/work-requests" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition font-medium">
                📊 Все заявки
            </a>
            <a href="/admin" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition font-medium">
                ⚙️ Filament Админка
            </a>
        </div>
    </div>

    <!-- Информация о системе -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-800 mb-2">🚀 Система готова к работе!</h3>
        <p class="text-blue-700">
            Бэкенд полностью настроен. Frontend на Livewire готов к разработке.
            Используйте Filament админку для управления данными или создавайте интерфейсы в Livewire компонентах.
        </p>
    </div>
</div>
