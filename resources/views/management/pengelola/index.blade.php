<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Data Pengelola
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
            <div class="mb-4 rounded-md bg-green-100 p-4 text-green-800">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-100 p-4 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="mb-4 flex justify-end">
                <a href="{{ route('management.managers.create') }}"
                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    + Tambah Pengelola
                </a>
            </div>

            <div class="overflow-x-auto rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">No.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Jabatan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Unit</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pengelola as $akun)
                        <tr>
                            <td class="px-4 py-3">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $akun->name }}</td>
                            <td class="px-4 py-3">{{ $akun->email }}</td>
                            <td class="px-4 py-3">
                                @switch($akun->role->value)
                                @case('kepala_unit')
                                Kepala Unit
                                @break
                                @case('sdm')
                                SDM
                                @break
                                @case('pengurus')
                                Pengurus
                                @break
                                @endswitch
                            </td>
                            <td class="px-4 py-3">
                                {{ $akun->unit ? str_replace('_', ' ', $akun->unit->value) : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($akun->is_active)
                                <span style="color: #16a34a; font-weight: 600;">Aktif</span>
                                @else
                                <span style="color: #dc2626; font-weight: 600;">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('management.managers.edit', $akun) }}"
                                    style="display: inline-block; background-color: #2563eb; color: #ffffff; padding: 8px 14px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                Belum ada akun pengelola.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>