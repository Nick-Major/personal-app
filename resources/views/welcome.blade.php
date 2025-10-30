<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased dark:bg-black text-white/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
                    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                        @auth
                            <a href="{{ url('/home') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-2 focus:rounded-sm focus:outline-red-500">Home</a>
                        @else
                            <a href="/login" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>
                            <a href="/register" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endauth
                    </div>
                </header>

                <main class="flex flex-col lg:flex-row w-full gap-4 lg:gap-8">
                    <div class="lg:py-24 flex-1">
                        <div class="flex flex-col items-start">
                            <div class="flex items-center mb-8 lg:mb-12 gap-4">
                                <svg class="w-10 h-10 lg:w-12 lg:h-12" viewBox="0 0 50 50">
                                    <path d="M9.656 17.977l14.508-8.388L38.344 17.977 23.836 26.365 9.656 17.977zm0 4.196v11.654l14.18 8.196 14.508-8.388V22.173l-14.18 8.196-14.508-8.196z" fill="#FF2D20"/>
                                </svg>
                                <h1 class="text-3xl/snug sm:text-4xl/snug lg:text-5xl/tight font-black text-black dark:text-white font-poppins">
                                    {{ config('app.name', 'Laravel') }}
                                </h1>
                            </div>

                            <div class="space-y-4 lg:space-y-6 max-w-lg text-lg lg:text-xl text-black/75 dark:text-white/75">
                                <p>
                                    Система управления персоналом для эффективного планирования и учета работ.
                                </p>
                                <p>
                                    Создавайте заявки, назначайте исполнителей, управляйте сменами и расчетами оплаты.
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 mt-8 lg:mt-12">
                                <a href="/login" class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-linear-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-2 focus:outline-red-500">
                                    <div class="flex-1">
                                        <div class="h-10 w-10 bg-[#FF2D20] dark:bg-red-800 flex items-center justify-center rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                            </svg>
                                        </div>

                                        <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">Войти в систему</h2>

                                        <p class="mt-2 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                            Доступ для сотрудников и подрядчиков
                                        </p>
                                    </div>
                                </a>

                                <a href="/admin" class="scale-100 p-6 bg-white dark:bg-gray-800/50 dark:bg-linear-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline-2 focus:outline-red-500">
                                    <div class="flex-1">
                                        <div class="h-10 w-10 bg-[#FF2D20] dark:bg-red-800 flex items-center justify-center rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-white">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                            </svg>
                                        </div>

                                        <h2 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">Админ-панель</h2>

                                        <p class="mt-2 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                            Управление системой для администраторов
                                        </p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-335/376 lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
                        <div class="absolute inset-0 bg-[url('https://laravel.com/img/logomark.min.svg')] bg-no-repeat bg-center mix-blend-luminosity">
                        </div>

                        <div class="absolute inset-0 bg-[url('https://laravel.com/img/grid.min.svg')] bg-center mask-[linear-gradient(180deg,white,rgba(255,255,255,0))]">
                        </div>

                        <div class="absolute inset-0 bg-linear-to-r from-[#FF2D20]/20 to-[#FF2D20]/5 dark:from-[#FF2D20]/10 dark:to-[#FF2D20]/5">
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                </footer>
            </div>
        </div>
    </body>
</html>
