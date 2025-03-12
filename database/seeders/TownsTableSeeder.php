<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Town;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TownsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        {
            $towns = [
                'Damascus' => ['Mazzeh', 'Kafr Souseh', 'Al-Malki', 'Baramkeh'],
                'Aleppo' => ['Al-Ashrafiyeh', 'Al-Seryan', 'Al-Hamadaniyah'],
                'Homs' => ['Al-Waer', 'Baba Amr', 'Al-Khalidiyah'],
                'Latakia' => ['Al-Slaibeh', 'Al-Mashrou’ Al-Sabe’a'],
                'Tartous' => ['Al-Raml Al-Janoubi', 'Baniyas']
            ];

            foreach ($towns as $cityName => $townList) {
                $city = City::where('name', $cityName)->first();
                if ($city) {
                    foreach ($townList as $town) {
                        Town::create([
                            'name' => $town,
                            'city_id' => $city->id
                        ]);
                    }
                }
            }
    }
}}
