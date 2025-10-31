<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Personal Management System</title>
    
    <!-- CDN для Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'status-confirmed': '#10B981',
                        'status-pending': '#F59E0B', 
                        'status-rejected': '#EF4444',
                    }
                }
            }
        }
    </script>
    
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div id="app">
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-800">Personal Management System</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <span class="text-gray-600">Привет, {{ auth()->user()->name }}!</span>
                            <a href="/" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Главная</a>
                            
                            @if(auth()->user()->hasRole('initiator'))
                                <a href="/planning" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Планирование</a>
                                <a href="/work-requests" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Мои заявки</a>
                            @endif
                            
                            @if(auth()->user()->hasRole('executor'))
                                <a href="/executor" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Мои назначения</a>
                            @endif
                            
                            <a href="/admin" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Админка</a>
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Выйти</button>
                            </form>
                        @else
                            <a href="/login" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md">Войти</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
