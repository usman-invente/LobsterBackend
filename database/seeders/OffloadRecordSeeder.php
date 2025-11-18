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
                'boatName' => 'Ocean Hunter',
                'offloadDate' => '2025-11-15',
                'tripNumber' => 'TRIP-001',
                'externalFactory' => 'Factory A',
                'totalKgOffloaded' => 125.50,
                'totalKgReceived' => 125.50,
                'totalKgDead' => 2.50,
                'totalKgRotten' => 1.00,
                'sizeU' => 15.00,
                'sizeA' => 30.50,
                'sizeB' => 35.00,
                'sizeC' => 25.00,
                'sizeD' => 15.00,
                'sizeE' => 5.00,
                'user_id' => $user->id,
            ],
            [
                'boatName' => 'Sea Warrior',
                'offloadDate' => '2025-11-16',
                'tripNumber' => 'TRIP-002',
                'externalFactory' => 'Factory B',
                'totalKgOffloaded' => 110.25,
                'totalKgReceived' => 110.25,
                'totalKgDead' => 1.75,
                'totalKgRotten' => 0.50,
                'sizeU' => 12.00,
                'sizeA' => 28.25,
                'sizeB' => 32.00,
                'sizeC' => 22.00,
                'sizeD' => 12.00,
                'sizeE' => 4.00,
                'user_id' => $user->id,
            ],
            [
                'boatName' => 'Blue Marlin',
                'offloadDate' => '2025-11-17',
                'tripNumber' => 'TRIP-003',
                'externalFactory' => 'Factory C',
                'totalKgOffloaded' => 145.75,
                'totalKgReceived' => 145.75,
                'totalKgDead' => 3.00,
                'totalKgRotten' => 1.25,
                'sizeU' => 18.00,
                'sizeA' => 35.75,
                'sizeB' => 40.00,
                'sizeC' => 28.00,
                'sizeD' => 18.00,
                'sizeE' => 6.00,
                'user_id' => $user->id,
            ],
        ];

        foreach ($offloadRecords as $record) {
            OffloadRecord::create($record);
        }

        $this->command->info('Offload records seeded successfully!');
    }
}