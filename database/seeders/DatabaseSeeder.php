<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\Region;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $regions = [
            "Autonomous Republic of Crimea",
            "Cherkasy Oblast",
            "Chernihiv Oblast",
            "Chernivtsi Oblast",
            "Dnipropetrovsk Oblast",
            "Donetsk Oblast",
            "Ivano-Frankivsk Oblast",
            "Kharkiv Oblast",
            "Kherson Oblast",
            "Khmelnytskyi Oblast",
            "Kirovohrad Oblast",
            "Kyiv Oblast",
            "Luhansk Oblast",
            "Lviv Oblast",
            "Mykolaiv Oblast",
            "Odessa Oblast",
            "Poltava Oblast",
            "Rivne Oblast",
            "Sumy Oblast",
            "Ternopil Oblast",
            "Vinnytsia Oblast",
            "Volyn Oblast",
            "Zakarpattia Oblast",
            "Zaporizhzhia Oblast",
            "Zhytomyr Oblast"
        ];

        $country = Country::create([
             'country' => 'Ukraine',
         ]);

        foreach ($regions as $region) {
           $created_region = Region::create([
                'region' => $region,
                'country_id' => $country->id,
            ]);
        }
    }
}
