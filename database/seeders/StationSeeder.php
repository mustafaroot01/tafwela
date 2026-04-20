<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\StationStatus;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            ['name' => 'Al-Mansour Station',      'name_ar' => 'محطة المنصور',         'lat' => 33.3152, 'lng' => 44.3661, 'city' => 'Baghdad', 'district' => 'المنصور'],
            ['name' => 'Karada Fuel Station',      'name_ar' => 'محطة الكرادة',         'lat' => 33.3044, 'lng' => 44.4008, 'city' => 'Baghdad', 'district' => 'الكرادة'],
            ['name' => 'Zayouna Station',          'name_ar' => 'محطة الزيونة',         'lat' => 33.3326, 'lng' => 44.4620, 'city' => 'Baghdad', 'district' => 'الزيونة'],
            ['name' => 'Sadr City Fuel',           'name_ar' => 'وقود مدينة الصدر',     'lat' => 33.3691, 'lng' => 44.4972, 'city' => 'Baghdad', 'district' => 'مدينة الصدر'],
            ['name' => 'Adhamiyah Station',        'name_ar' => 'محطة الأعظمية',        'lat' => 33.3720, 'lng' => 44.3963, 'city' => 'Baghdad', 'district' => 'الأعظمية'],
            ['name' => 'Saidiya Fuel Center',      'name_ar' => 'مركز وقود السيدية',    'lat' => 33.2725, 'lng' => 44.3480, 'city' => 'Baghdad', 'district' => 'السيدية'],
            ['name' => 'Dora Station',             'name_ar' => 'محطة الدورة',          'lat' => 33.2587, 'lng' => 44.4051, 'city' => 'Baghdad', 'district' => 'الدورة'],
            ['name' => 'Bayaa Station',            'name_ar' => 'محطة البياع',          'lat' => 33.2935, 'lng' => 44.3267, 'city' => 'Baghdad', 'district' => 'البياع'],
            ['name' => 'Jihad District Station',   'name_ar' => 'محطة حي الجهاد',       'lat' => 33.2820, 'lng' => 44.3394, 'city' => 'Baghdad', 'district' => 'حي الجهاد'],
            ['name' => 'Shaab Stadium Fuel',       'name_ar' => 'وقود الشعب',           'lat' => 33.3601, 'lng' => 44.4237, 'city' => 'Baghdad', 'district' => 'الشعب'],
            ['name' => 'Hay Al-Jamia Station',     'name_ar' => 'محطة حي الجامعة',      'lat' => 33.3219, 'lng' => 44.3528, 'city' => 'Baghdad', 'district' => 'حي الجامعة'],
            ['name' => 'Palestine Street Fuel',    'name_ar' => 'وقود شارع فلسطين',     'lat' => 33.3482, 'lng' => 44.4156, 'city' => 'Baghdad', 'district' => 'شارع فلسطين'],
        ];

        $fuelOptions = ['available', 'limited', 'unavailable'];
        $congestions = ['low', 'medium', 'high'];

        foreach ($stations as $data) {
            $station = Station::updateOrCreate(
                ['name' => $data['name']],
                [
                    'name_ar'   => $data['name_ar'],
                    'latitude'  => $data['lat'],
                    'longitude' => $data['lng'],
                    'city'      => $data['city'],
                    'district'  => $data['district'] ?? null,
                    'is_active' => true,
                ]
            );

            StationStatus::updateOrCreate(
                ['station_id' => $station->id],
                [
                    'petrol'          => $fuelOptions[array_rand($fuelOptions)],
                    'diesel'          => $fuelOptions[array_rand($fuelOptions)],
                    'kerosene'        => $fuelOptions[array_rand($fuelOptions)],
                    'gas'             => $fuelOptions[array_rand($fuelOptions)],
                    'congestion'      => $congestions[array_rand($congestions)],
                    'source'          => 'admin',
                    'last_updated_at' => now(),
                ]
            );
        }
    }
}
