<?php

namespace Database\Seeders;

use App\Models\Expo;
use App\Models\Sponsor;
use Illuminate\Database\Seeder;

class Expo2026Seeder extends Seeder
{
    public function run(): void
    {
        // Update (or create) the 2026 expo with official flyer data
        $expo = Expo::updateOrCreate(
            ['id' => 1],  // update whichever record is ID 1
            [
                'name'          => 'Insiza District Industrial Expo 2026',
                'year'          => 2026,
                'start_date'    => '2026-09-16',
                'end_date'      => '2026-09-18',
                'venue'         => 'Filabusi Show Grounds',
                'theme'         => 'Connect, Collaborate and Grow',
                'description'   => '3 Days of Innovation, Opportunity & Growth! Showcasing Mining, Agriculture, Education and Organisations. Activities include Exhibitions, Product Showcases, Networking, Workshops & Seminars, Demonstrations, Business Matchmaking and Entertainment for all.',
                'contact_phone' => '+263774381008',
                'contact_email' => 'info@insizaexpo.co.zw',
                'is_active'     => true,
            ]
        );

        // Deactivate all other expos
        Expo::where('id', '!=', $expo->id)->update(['is_active' => false]);

        // Replace sponsors for this expo with the ones from the flyer
        Sponsor::where('expo_id', $expo->id)->delete();

        $sponsors = [
            ['name' => 'Moxen Investments',            'sort_order' => 1],
            ['name' => 'Gloryness Signs',               'sort_order' => 2],
            ['name' => 'Reliable Agriculture Services', 'sort_order' => 3],
            ['name' => 'Ivinar Park Academy',           'sort_order' => 4],
            ['name' => 'New Eclipse 11 Mine',           'sort_order' => 5],
        ];

        foreach ($sponsors as $s) {
            Sponsor::create([
                'expo_id'    => $expo->id,
                'name'       => $s['name'],
                'sort_order' => $s['sort_order'],
                'tier'       => 'partner',
            ]);
        }

        $this->command->info("2026 Expo seeded: {$expo->name} (ID {$expo->id}, active)");
        $this->command->info('Sponsors: ' . Sponsor::where('expo_id', $expo->id)->count());
    }
}
