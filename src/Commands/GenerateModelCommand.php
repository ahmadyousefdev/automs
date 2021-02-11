<?php

namespace Ahmadyousefdev\Automs\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automs:generate_model {class_name? : The name of the class.} {--file_name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating the model for the given name';

    
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

        // Generates the model Class File
        $this->generateModelFile();

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
     * Generates the Model file
     *
     * @return void
     */
    protected function generateModelFile()
    {
        // Set the origin and destination for the model class file
        $fileOrigin = __DIR__ . '/../stubs/model.stub';

        // file destination 
        $fileDestination = base_path('/app/Models/' . $this->class_name . '.php');

        if ($this->file->exists($fileDestination)) {
            $this->info('This file already exists: ' . $fileDestination);
            $this->info('Aborting file creation.');
            return false;
        }

        // Get the original string content of the file
        $stubContent = $this->file->get($fileOrigin);
        
        // Here we will generate the fillables from the model file
        if($this->option('file_name') != null)
        {
            // Get the fillables
            $fillables = $this->generateFillables($this->option('file_name'));
        }
        else {
            $fillables = null;
        }

        // replace the variables
        $stubContent = str_replace('{{ class_name }}', $this->class_name, $stubContent);
        $stubContent = str_replace('{{ namespace }}', 'App\Models', $stubContent);
        $stubContent = str_replace('{{ fillables }}', $fillables, $stubContent);
        
        // Put the content into the destination directory
        $this->file->put($fileDestination, $stubContent);

        $this->info('model Created: ' . $fileDestination);
    }

    protected function generateFillables($file_name)
    {
        $model_names_file = __DIR__ . '/../Models/'.$file_name.'.json';
        $data = file_get_contents($model_names_file);

        $collection = collect(json_decode($data, true));

        $fillables = "";
        foreach ($collection['fields'] as $field)
        {
            $fillables .= "\r\n\t\t\t'".$field['name']."',";
        }
        return $fillables;
    }
}