<?php

namespace Ahmadyousefdev\Automs\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automs:generate_migration {class_name? : The name of the class.} {--file_name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating the migration for the model';

    
    /**
     * Hide this command from the list
     *
     * @var bool
     */
    protected $hidden = true;

    /**
     * Our custom class properties here!
     */
    protected $class_name;
    protected $file;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->file = new Filesystem();
    }   

    /**
     * Adds content to a given file.
     *
     * @param  string  $file
     *
     * @return $this
     */
    protected function putContentInFile($file, $content)
    {
		$path = dirname($file);
				
		if(!Storage::exists($path)) {
			Storage::makeDirectory($path, 0755, true);
		}
		
        Storage::put($file, $content);
        
        $this->file->put($file, $content);

        return $this;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Gathers all parameters
        $this->gatherParameters();

        // Generates the migration Class File
        $this->generateMigrationFile();

    }

    /**
     * Gather all necessary parameters
     *
     * @return void
     */
    protected function gatherParameters()
    {
        $this->class_name = $this->argument('class_name');

        // If you didn't input the name of the class
        if (!$this->class_name) {
            $this->class_name = $this->ask('Enter class name');
        }

        // Convert to studly case
        $this->class_name = Str::studly($this->class_name);
    }

    /**
     * Generates the Migration file
     *
     * @return void
     */
    protected function generateMigrationFile()
    {
        // Set the origin and destination for the migration class file
        $fileOrigin = __DIR__ . '/../stubs/migration.create.stub';

        // Strings
        $migrationClassName = 'Create'.Str::plural(Str::studly($this->class_name)).'Table';
        $migrationClassPlural = Str::plural(Str::snake(trim($this->class_name)));

        // Generating a name for the migration
        $migration_name = \Carbon\Carbon::now()->format('Y_m_d_His').'_create_'.$migrationClassPlural.'_table';
        // file destination 
        $fileDestination = base_path('/database/migrations/' . $migration_name . '.php');

        if ($this->file->exists($fileDestination)) {
            $this->info('This file already exists: ' . $fileDestination);
            $this->info('Aborting file creation.');
            return false;
        }

        // Get the original string content of the file
        $stubContent = $this->file->get($fileOrigin);
        
        $stubContent = str_replace('{{ class }}', $migrationClassName, $stubContent);
        $stubContent = str_replace('{{ table }}', $migrationClassPlural, $stubContent);
        // Here we will generate the fillables from the model file
        if($this->option('file_name') != null)
        {
            // Get the fillables
            $fillables = $this->generateFillables($this->option('file_name'));
        }
        else {
            $fillables = null;
        }
        $stubContent = str_replace('{{ fillables }}', $fillables, $stubContent);
        // Put the content into the destination directory
        $this->file->put($fileDestination, $stubContent);

        $this->info('Migration Created: ' . $fileDestination);
    }

    protected function generateFillables($file_name)
    {
        $model_names_file = __DIR__ . '/../Models/'.$file_name.'.json';
        $data = file_get_contents($model_names_file);

        $collection = collect(json_decode($data, true));
        //dd($collection);
        $fillables = "";
        foreach ($collection['fields'] as $field)
        {
            // $table->text('profile_photo_path')->nullable();
            // dd($field['name']);
            $nullable = $field['nullable'] == true ? '->nullable()' : '';
            $fillables .= "\r\n\t\t\t".'$table->'.$field['data-type']."('".$field['name']."')".$nullable.';';
        }
        return $fillables;
    }
}