<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Seluruh Karyawan Kosudgama') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Pesan sukses --}}
            @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            {{-- Pesan error --}}
            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">
                            Data Karyawan & Jatah Cuti {{ now()->year }}
                        </h3>
                    </div>
                    @if (auth()->user()->role === \App\Enums\UserRole::SDM)
                    <div class="mb-4 flex justify-end">
                        <a
                            href="{{ route('management.users.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            + Tambah Karyawan
                        </a>
                    </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">

                            <thead>
                                <tr>
                                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        Nama Karyawan
                                    </th>

                                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        Email
                                    </th>

                                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">
                                        Unit Kerja
                                    </th>

                                    <th class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">
                                        Jatah
                                    </th>

                                    <th class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">
                                        Terpakai
                                    </th>

                                    <th class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">
                                        Sisa
                                    </th>
                                    <th class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">
                                        Status Akun
                                    </th>
                                    <th class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase">
                                        Aksi
                                    </th>

                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">

                                @forelse ($karyawan as $user)

                                @php
                                $quota = $user->leaveQuotas->first();

                                $totalQuota = $quota?->total_quota ?? 12;
                                $usedQuota = $quota?->used_quota ?? 0;
                                $remainingQuota = max(0, $totalQuota - $usedQuota);
                                @endphp

                                <tr>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ $user->name }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ $user->email }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap">
                                        {{ $user->unit ? ucwords(str_replace('_', ' ', $user->unit->name)) : 'N/A' }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap text-center font-semibold">
                                        {{ $totalQuota }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        {{ $usedQuota }}
                                    </td>

                                    <td class="px-4 py-4 whitespace-nowrap text-center font-semibold">
                                        {{ $remainingQuota }}
                                    </td>
                                    <td class="text-center">
                                        @if ($user->is_active)
                                        <span style="color: #16a34a; font-weight: 600;">Aktif</span>
                                        @else
                                        <span style="color: #dc2626; font-weight: 600;">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        @if (auth()->user()->role === \App\Enums\UserRole::SDM)
                                        <a
                                            href="{{ route('management.users.edit', $user) }}"
                                            style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                                            Edit Akun
                                        </a>
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                        <form
                                            action="{{ route('management.users.status', $user) }}"
                                            method="POST"
                                            style="display: inline-block; margin-left: 6px;"
                                            onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan kembali' }} akun {{ addslashes($user->name) }}?')">
                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="hidden"
                                                name="is_active"
                                                value="{{ $user->is_active ? 0 : 1 }}">

                                            <button
                                                type="submit"
                                                style="display: inline-block; background-color: {{ $user->is_active ? '#dc2626' : '#16a34a' }}; color: #ffffff; padding: 8px 14px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer;">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan Kembali' }}
                                            </button>
                                        </form>
                                    </td>

                                </tr>

                                @empty

                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data karyawan.
                                    </td>
                                </tr>

                                @endforelse

                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>