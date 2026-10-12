<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sistem Cuti Kosudgama</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-gray-100">
            
            <div class="w-full max-w-md p-8 bg-white shadow-xl rounded-lg">
                
                <div class="flex justify-center mb-6">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>

                <h2 class="text-center text-2xl font-bold text-gray-800 mb-8">
                    Sistem Pengajuan Cuti
                    <span class="block text-lg font-normal text-gray-600">Koperasi Kosudgama</span>
                </h2>

                <div class="space-y-4">
                    
                    <a href="{{ route('login') }}" 
                       class="block w-full px-4 py-3 bg-indigo-600 text-white text-center font-semibold rounded-lg shadow-md hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-opacity-75 transition duration-200">
                        MASUK (LOGIN)
                    </a>
                    
                    <a href="{{ route('register') }}" 
                       class="block w-full px-4 py-3 bg-gray-200 text-gray-800 text-center font-semibold rounded-lg shadow-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-75 transition duration-200">
                        DAFTAR AKUN BARU
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>