<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\OffloadRecord;
class OffloadRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        $offloadRecords = [
            [
                'date' => '2025-11-15',
                'boatName' => 'Ocean Hunter',
                'boatNumber' => 'OH-001',
                'captainName' => 'John Smith',
                'totalCrates' => 50,
                'totalKgAlive' => 125.50,
                'sizeU' => 15.00,
                'sizeA' => 30.50,
                'sizeB' => 35.00,
                'sizeC' => 25.00,
                'sizeD' => 15.00,
                'sizeE' => 5.00,
                'deadOnTanks' => 2.50,
                'rottenOnTanks' => 1.00,
                'notes' => 'Good quality catch from northern waters',
                'user_id' => $user->id,
            ],
            [
                'date' => '2025-11-16',
                'boatName' => 'Sea Warrior',
                'boatNumber' => 'SW-002',
                'captainName' => 'Mike Johnson',
                'totalCrates' => 45,
                'totalKgAlive' => 110.25,
                'sizeU' => 12.00,
                'sizeA' => 28.25,
                'sizeB' => 32.00,
                'sizeC' => 22.00,
                'sizeD' => 12.00,
                'sizeE' => 4.00,
                'deadOnTanks' => 1.75,
                'rottenOnTanks' => 0.50,
                'notes' => 'Early morning catch, excellent condition',
                'user_id' => $user->id,
            ],
            [
                'date' => '2025-11-17',
                'boatName' => 'Blue Marlin',
                'boatNumber' => 'BM-003',
                'captainName' => 'David Chen',
                'totalCrates' => 60,
                'totalKgAlive' => 145.75,
                'sizeU' => 18.00,
                'sizeA' => 35.75,
                'sizeB' => 40.00,
                'sizeC' => 28.00,
                'sizeD' => 18.00,
                'sizeE' => 6.00,
                'deadOnTanks' => 3.00,
                'rottenOnTanks' => 1.25,
                'notes' => 'Large haul from deep sea fishing',
                'user_id' => $user->id,
            ],
        ];

        foreach ($offloadRecords as $record) {
            OffloadRecord::create($record);
        }

        $this->command->info('Offload records seeded successfully!');
    }
}
