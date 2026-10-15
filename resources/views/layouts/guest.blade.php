<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Cuti Kosudgama') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        
        <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
            
            <div class="mb-4">
                <a href="/">
                    <x-application-logo class="w-30 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">
                    Sistem Informasi Koperasi Kosudgama
                </h1>
                <p class="text-gray-600">
                    Manajemen cuti, absensi, dan data karyawan.
                </p>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-6 py-8 bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>