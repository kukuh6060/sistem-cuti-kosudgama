<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Enums\UserUnit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class BuatSdmPertama extends Command
{
    protected $signature = 'app:buat-sdm';

    protected $description = 'Membuat akun SDM pertama jika belum tersedia';

    public function handle(): int
    {
        // Hentikan jika akun SDM sudah ada.
        if (User::where('role', UserRole::SDM->value)->exists()) {
            $this->error('Akun SDM sudah ada. Pembuatan dibatalkan.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama SDM'));
        $email = trim((string) $this->ask('Email SDM'));
        $password = $this->secret('Kata sandi SDM');
        $confirmation = $this->secret('Ulangi kata sandi SDM');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->role = UserRole::SDM;
        $user->unit = UserUnit::SDM;
        $user->is_active = true;
        $user->save();

        $this->info('Akun SDM pertama berhasil dibuat.');
        $this->info('Silakan login menggunakan email yang didaftarkan.');

        return self::SUCCESS;
    }
}
