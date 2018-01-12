<?php

namespace App\Console\Commands;

use App\Photo;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PhotoResizer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photo-resizer {exec}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var GuzzleHttpClient
     */
    protected $httpClient;

    protected $dimensions = [
        '320x240' => ['width' => 320, 'height' => 240],
        '384x288' => ['width' => 384, 'height' => 288],
        '640x480' => ['width' => 640, 'height' => 480],
    ];

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->httpClient = new GuzzleHttpClient();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $exec = $this->argument('exec');

        switch ($exec) {
            case 'download' :
                $this->download();
                break;

            case 'generate' :
                $this->generate();
                break;

            default :
                echo "Choose 'download' or 'generate' for this command";
                break;
        }
    }

    /**
     * Download and Store photos from json list
     */
    private function download()
    {
        $response = $this->httpClient->request('GET', env('PHOTO_ENDPOINT'));
        $content = json_decode($response->getBody()->getContents());

        if (! json_last_error() and property_exists($content, 'images')) {

            $requests = function ($images) {
                foreach ($images as $image) {
                    yield new \GuzzleHttp\Psr7\Request('GET', $image->url);
                }
            };

            $pool = new Pool($this->httpClient, $requests($content->images), [
                'concurrency' => count($content->images),
                'fulfilled' => function ($response, $index) use ($content) {
                    $exp = explode('/', $content->images[$index]->url);

                    $imageId = end($exp);

                    $fileName = env('PHOTO_PATH') . $imageId;

                    if (! Storage::exists($fileName)) {
                        Storage::put($fileName, $response->getBody()->getContents(), 'public');
                    }
                },
                'rejected' => function ($reason, $index) {
                    die('fail');
                },
            ]);

            $promise = $pool->promise();
            $promise->wait();
        }
    }

    /**
     * Generate original photos in others dimensions
     */
    private function generate()
    {
        $originalImages = Storage::files(env('PHOTO_PATH'));

        foreach ($originalImages as $originalImage) {
            $pieces = explode('/', $originalImage);

            $exp = explode('.', end($pieces));

            $extension = end($exp);

            array_pop($exp);

            $imageId = implode('.', $exp);

            $photos = [];

            foreach ($this->dimensions as $key => $dimension) {
                $fileName = env('PHOTO_PATH_RESIZE') . "{$imageId}_{$key}.$extension";

                if (! Storage::exists($fileName)) {
                    $img = Image::make(Storage::get($originalImage));
                    $img->resize($dimension['width'], $dimension['height']);

                    Storage::put($fileName, (string)$img->encode(), 'public');

                    if (Storage::exists($fileName)) {
                        $photos[$key] = asset(Storage::url($fileName));
                    }
                }
            }

            /**
             * Save on Storage
             */
            if (count($photos)) {
                $photo = new Photo();
                $photo->name = $imageId;
                $photo->photos = $photos;
                $photo->photos = $photos;
                $photo->save();
            }
        }
    }
}
