<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Event::create([
            'name' => 'Cafe Sawah',
            'description' => 'Go join to get some Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'type' => 'Guitarist',
            'date' => '2020-12-10 05:45:52',
            'fee' => '4300000',
            'address' => array(
                'name'      => 'ACE Hardware - Majapahit',
                'address'   => 'Jl. Brigjen Sudiarto No.276, Kalicari, Kec. Pedurungan, Kota Semarang, Jawa Tengah 50198, Indonesia',
                'place_id'  => 'ChIJwXJ4mfGMcC4R_NG5VZwEEWU"',
                'lat'       => '-7.006230800000001',
                'lng'       => '110.4571644'
            ),
            'created_by' => 1,
            'photo_urls' => array('petr-sevcovic-qE1jxYXiwOA-unsplash.jpg'),
        ]);

        Event::create([
            'name' => 'Sigolo Cafe',
            'description' => 'Go join to get some Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'type' => 'Singer',
            'date' => '2020-12-10 05:45:52',
            'fee' => '3300000',
            'address' => array(
                'name'      => 'ACE Hardware - Majapahit',
                'address'   => 'Jl. Brigjen Sudiarto No.276, Kalicari, Kec. Pedurungan, Kota Semarang, Jawa Tengah 50198, Indonesia',
                'place_id'  => 'ChIJwXJ4mfGMcC4R_NG5VZwEEWU"',
                'lat'       => '-7.006230800000001',
                'lng'       => '110.4571644'
            ),
            'created_by' => 1,
            'photo_urls' => array('tony-lee-8IKf54pc3qk-unsplash.jpg'),
        ]);
    }
}
