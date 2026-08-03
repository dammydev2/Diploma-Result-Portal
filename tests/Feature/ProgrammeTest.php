<?php

namespace Tests\Feature;

use App\Enums\ProgrammeLevel;
use App\Models\Programme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammeTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_with_level_disambiguates_same_named_programmes(): void
    {
        $nd = Programme::factory()->create(['name' => 'Computer Science', 'level' => ProgrammeLevel::NationalDiploma]);
        $hnd = Programme::factory()->create(['name' => 'Computer Science', 'level' => ProgrammeLevel::HigherNationalDiploma]);

        $this->assertSame('Computer Science (ND)', $nd->name_with_level);
        $this->assertSame('Computer Science (HND)', $hnd->name_with_level);
        $this->assertNotSame($nd->name_with_level, $hnd->name_with_level);
    }
}
