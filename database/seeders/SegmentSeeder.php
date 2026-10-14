<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Segment;

class SegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      $segments = [
        ['code' => 'PV', 'name' => 'Personal Vehicle'],
        ['code' => 'CV', 'name' => 'Commercial Vehicle'],
        ['code' => 'EV', 'name' => 'Electric Vehicle'],
      ];

      foreach ($segments as $segment) {
        Segment::updateOrCreate(
          ['code' => $segment['code']],
          $segment
        );
      }
    }
}
