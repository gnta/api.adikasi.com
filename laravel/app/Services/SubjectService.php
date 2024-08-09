<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Str;

class SubjectService
{
    static function get(string $name): Subject
    {
        $slug = Str::slug($name);

        return Subject::firstOrCreate(['slug' => $slug], [
            'name' => $name,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
