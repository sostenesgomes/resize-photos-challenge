<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhotoResizer extends TestCase
{
    /**
     * A Download photo test.
     *
     * @return void
     */
    public function testDownload()
    {
        $this->artisan('photo-resizer', ['exec' => 'download']);
        $this->assertDirectoryExists(storage_path('app/photos/test'));
    }

    public function testGenerate()
    {
        $this->artisan('photo-resizer', ['exec' => 'generate']);
        $this->assertDirectoryExists(storage_path('app/public/test'));
    }

    public function testGetPhotos()
    {
        $response = $this->get('/api/photos');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => [
                '_id',
                'name',
                'photos',
            ]
        ]);
    }

}
