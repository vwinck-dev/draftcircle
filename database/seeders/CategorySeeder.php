<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = app()->getLocale() === 'pt_BR'
            ? ['engineering' => 'Engenharia', 'law' => 'Direito', 'health' => 'Saúde', 'information-technology' => 'Tecnologia da Informação', 'education' => 'Educação', 'mathematics' => 'Matemática', 'languages' => 'Idiomas']
            : ['engineering' => 'Engineering', 'law' => 'Law', 'health' => 'Health', 'information-technology' => 'Information Technology', 'education' => 'Education', 'mathematics' => 'Mathematics', 'languages' => 'Languages'];

        foreach ($categories as $slug => $name) {
            Category::query()->updateOrCreate(attributes: ['slug' => $slug], values: ['name' => $name]);
        }
    }
}
