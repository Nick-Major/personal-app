<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Management System</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Tailwind CSS через CDN (временно) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                    }
                }
            }
        }
    </script>
    
    <!-- Livewire Scripts -->
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex h-screen">
        <!-- Боковое меню -->
        <div class="w-64 bg-white shadow-lg shrink-0">
            <div class="p-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">Personal System</h1>
                <p class="text-sm text-gray-600 mt-1">{{ auth()->user()->getUserTypeAttribute() }}</p>
            </div>
            
            <nav class="mt-4">
                @if(auth()->user()->hasRole(['initiator', 'admin']))
                    <!-- Меню для Инициатора -->
                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">ЛК Инициатора</div>
                    <a href="/planning" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200 {{ request()->is('planning') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <span class="truncate">Планирование</span>
                    </a>
                    <a href="/work-requests" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200 {{ request()->is('work-requests*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="truncate">Заявки</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole('executor'))
                    <!-- Меню для Исполнителя -->
                    <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">ЛК Исполнителя</div>
                    <a href="/executor" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200 {{ request()->is('executor') && !request()->is('executor/*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="truncate">Профиль</span>
                    </a>
                    <a href="/executor/requests" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200 {{ request()->is('executor/requests') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="truncate">Запросы на выход</span>
                    </a>
                @endif

                <!-- Выход -->
                <div class="border-t border-gray-200 mt-4 pt-4">
                    <a href="/logout" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-200" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="truncate">Выйти</span>
                    </a>
                    <form id="logout-form" action="/logout" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </nav>
        </div>

        <!-- Основной контент -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Хедер -->
            <header class="bg-white shadow-sm border-b border-gray-200 shrink-0">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            @yield('title', 'Dashboard')
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            @yield('subtitle', 'Обзор системы')
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-700">{{ auth()->user()->full_name }}</span>
                        <div class="w-8 h-8 bg-linear-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold shadow-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}{{ substr(auth()->user()->surname, 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Контент страницы -->
            <main class="flex-1 overflow-auto p-6 bg-gray-50">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
