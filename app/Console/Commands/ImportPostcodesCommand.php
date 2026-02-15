<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;

class ImportPostcodesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'import:postcodes {path=storage/app/postcodes_sample.csv}';

    /**
     * @var string
     */
    protected $description = 'Import UK postcodes from Free Map Tools into the DB.';

    public function handle()
    {
        $path = $this->argument('path');

        if (! File::exists($path)) {
            $this->error("File not found at: {$path}");
            return Command::FAILURE;
        }

        $this->info('Starting import...');

        LazyCollection::make(function () use ($path) {
            $handle = fopen($path, 'r');
            
            // Skip the header row
            fgetcsv($handle); 

            while (($row = fgetcsv($handle)) !== false) {
                yield $row;
            }

            fclose($handle);
        })
            ->chunk(500)
            ->each(function (LazyCollection $chunk): void {
                $upsertData = [];

                foreach ($chunk as $row) {
                    // Free Map Tools format: id, postcode, lat, lon
                    if (count($row) < 4) continue;

                    $postcode = strtoupper(str_replace(' ', '', $row[1]));
                    $latitude = $row[2];
                    $longitude = $row[3];

                    if (!is_numeric($latitude) || !is_numeric($longitude)) continue;

                    $upsertData[] = [
                        'postcode' => $postcode,
                        // MariaDB POINT expects (Longitude, Latitude)
                        'location' => DB::raw("POINT($longitude, $latitude)"),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('postcodes')->upsert($upsertData, ['postcode'], ['location', 'updated_at']);
            });

        $this->info('Import completed successfully!');
        return Command::SUCCESS;
    }
}
