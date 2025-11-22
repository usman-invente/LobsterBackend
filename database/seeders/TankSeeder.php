<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tank;
class TankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $tanks = [
            [
                'tankNumber' => 1,
                'tankName' => 'Tank A - North Wing',
                'status' => 1,
            ],
            [
                'tankNumber' => 2,
                'tankName' => 'Tank B - North Wing',
                'status' => 1,
            ],
            [
                'tankNumber' => 3,
                'tankName' => 'Tank C - South Wing',
                'status' => 1,
            ],
            [
                'tankNumber' => 4,
                'tankName' => 'Tank D - South Wing',
                'status' => 1,
            ],
            [
                'tankNumber' => 5,
                'tankName' => 'Tank E - Central',
                'status' => 1,
            ],
            [
                'tankNumber' => 6,
                'tankName' => 'Tank F - Central',
                'status' => 1,
            ],
            [
                'tankNumber' => 7,
                'tankName' => 'Tank G - Reserve',
                'status' => 0,
            ],
            [
                'tankNumber' => 8,
                'tankName' => 'Tank H - Reserve',
                'status' => 0,
            ],
        ];

        foreach ($tanks as $tank) {
            Tank::create($tank);
        }

        $this->command->info('Tank seeder completed successfully!');
    }
}
