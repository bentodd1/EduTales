<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeLevelSeeder extends Seeder
{
    public function run()
    {
        $gradeLevels = [
            ['name' => 'Preschool'],
            ['name' => 'Kindergarten'],
            ['name' => '1st Grade'],
            ['name' => '2nd Grade'],
            ['name' => '3rd Grade'],
            ['name' => '4th Grade'],
            ['name' => '5th Grade'],
            ['name' => '6th Grade'],
        ];

        DB::table('grade_levels')->insert($gradeLevels);
    }
}
