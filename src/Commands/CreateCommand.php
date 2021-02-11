<?php

namespace Ahmadyousefdev\Automs\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class CreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automs:create {name : the name of the model which will be generated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating a model and its component from a given name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        // Convert to Uppercase
        $name = Str::studly($name);
        // Search for the name in the model_names json file
        $result = $this->searchForName($name);
        // if the model is found generate the files
        if($result != null)
        {
            $this->info('Full model '.$name.' and its components were generated successfully');
            $fileString = ' --file_name='.$result['file_name'];
        }
        // else, generate a simple model and its components
        else
        {
            $this->info('Empty model '.$name.' and its components were generated successfully');
            $fileString = '';
        }

        // Generate migration
        Artisan::call('automs:generate_migration '. $name .$fileString);
        // Generate Model
        Artisan::call('automs:generate_model '. $name .$fileString);
        // Generate Routes (we have to figure out a way to check if exist in the file before generating it)
        Artisan::call('automs:generate_routes '. $name .$fileString);
        // Generate Controller
        Artisan::call('automs:generate_controller '. $name .$fileString);
        // Generate Views
        Artisan::call('automs:generate_views '. $name .$fileString);
        
        return 0;
    }

        
    /**
     * Searches for the model name inside models_names.json
     *
     * @param  mixed $name
     * @return void
     */
    protected function searchForName($name)
    {
        $model_names_file = __DIR__ . '/../model_names.json';
        $data = file_get_contents($model_names_file);

        $collection = collect(json_decode($data, true));

        $result = $collection->first(function ($key, $value) use ($name) {
            return collect($key['names'])->contains($name);
        });

        return $result;
    }
}
