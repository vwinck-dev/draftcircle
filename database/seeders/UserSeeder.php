<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $isPortuguese = app()->getLocale() === 'pt_BR';
        $locale = $isPortuguese ? 'pt_BR' : 'en';
        $faker = fake(locale: $isPortuguese ? 'pt_BR' : 'en_US');

        User::query()->updateOrCreate(
            attributes: ['email' => 'admin@draftcircle.local'],
            values: [
                'name' => $isPortuguese ? 'Administrador do DraftCircle' : 'DraftCircle Administrator',
                'registration_number' => 'ADM001',
                'role' => UserRole::Admin,
                'locale' => $locale,
                'email_verified_at' => now(),
                'password' => Hash::make(value: 'password'),
            ]
        );

        User::query()->updateOrCreate(
            attributes: ['email' => 'editor@draftcircle.local'],
            values: [
                'name' => $isPortuguese ? 'Editor Responsável' : 'Lead Editor',
                'registration_number' => 'LIB001',
                'role' => UserRole::Librarian,
                'locale' => $locale,
                'email_verified_at' => now(),
                'password' => Hash::make(value: 'password'),
            ]
        );

        foreach (range(start: 1, end: 80) as $index) {
            $paddedIndex = str_pad(
                string: (string) $index,
                length: 4,
                pad_string: '0',
                pad_type: STR_PAD_LEFT
            );

            User::query()->updateOrCreate(
                attributes: ['registration_number' => "STU{$paddedIndex}"],
                values: [
                    'name' => $faker->name(),
                    'email' => "writer{$index}@draftcircle.local",
                    'role' => UserRole::Student,
                    'locale' => $locale,
                    'email_verified_at' => now(),
                    'password' => Hash::make(value: 'password'),
                ]
            );
        }
    }
}
