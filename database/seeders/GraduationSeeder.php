<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GraduationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = array(
            array(
                'name' => 'Siswa X',
                'nisn' => '00000000',
                'birth_date' => '1997-05-21',
                'major' => 'Teknik Informatika',
            )
        );

        DB::table('graduations')->insert($data);
    }
}
