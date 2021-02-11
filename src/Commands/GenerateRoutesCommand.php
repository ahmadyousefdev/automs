<?php

namespace Ahmadyousefdev\Automs\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ahmadyousefdev\Automs\Commands\Helpers;

class GenerateRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automs:generate_routes {class_name? : The name of the class.} {--file_name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating the routes for the given model';

    
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
    protected $commonFunctions;

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
        $this->generateRoutes();

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
     * Generates the Routes
     *
     * @return void
     */
    protected function generateRoutes()
    {
        // getting the jetstream navigation file
        $routesFile = base_path('/routes/web.php');
        // Get the original content of the file
        $routesFileContent = $this->file->get($routesFile);
        // generate the navigation for the base url of the model
        $ControllerName = Str::ucfirst($this->class_name).'Controller';
        $useController ="use App\Http\Controllers\\".$ControllerName.';';
        
        // Adding the use method 
        $routesFileContent = Helpers::insertTextAfter($routesFileContent, 'use Illuminate\Support\Facades\Route;', $useController);
        // Put the new content in the place of the old one
        $this->file->put($routesFile, $routesFileContent);

        // Adding the routes
        // Get the routes stub
        $stubContent = $this->file->get(__DIR__ . '/../stubs/routes.stub');
        // replacing the variables
        $stubContent = str_replace('{{ model_name_plural }}', Str::plural(Str::snake(trim($this->class_name))), $stubContent);
        $stubContent = str_replace('{{ controller_name }}', $ControllerName, $stubContent);
        // Appending the stub content to the route file
        $this->file->append($routesFile,"\n".$stubContent);
    }
}