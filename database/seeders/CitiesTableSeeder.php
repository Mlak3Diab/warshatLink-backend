<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cities = ['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Tartous'];

        foreach ($cities as $city) {
            City::create(['name' => $city]);
        }
    }
}
