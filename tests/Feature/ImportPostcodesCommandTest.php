<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportPostcodesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_command_stores_data_correctly(): void
    {
        $disk = Storage::disk('local');
        $csvContent = "id,postcode,lat,lng\n1,M11AG,53.4808,-2.2426";
        $disk->put('test_postcodes.csv', $csvContent);
        
        $this->artisan('import:postcodes', ['path' => $disk->path('test_postcodes.csv')])
            ->assertExitCode(0);

        $this->assertDatabaseHas('postcodes', ['postcode' => 'M11AG']);
        
        $disk->delete('test_postcodes.csv');
    }
}