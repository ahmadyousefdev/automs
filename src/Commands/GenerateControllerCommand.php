<?php

namespace Ahmadyousefdev\Automs\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automs:generate_controller
    {class_name? : The name of the class.} {--file_name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating the controller for the model';

    
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

        // Generates the Controller Class File
        $this->generateControllerFile();
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
     * Generates the Controller file
     *
     * @return void
     */
    protected function generateControllerFile()
    {
        // Set the origin and destination for the Controller class file
        $fileOrigin = __DIR__ . '/../stubs/controller.model.stub';
        $fileDestination = base_path('/app/Http/Controllers/' . $this->class_name . 'Controller.php');
        $modelNameSmall = Str::snake(trim($this->class_name));
        $namespacedModel = "App\Models\\".$this->class_name;

        if ($this->file->exists($fileDestination)) {
            $this->info('This file already exists: ' . $fileDestination);
            $this->info('Aborting file creation.');
            return false;
        }

        // Get the original string content of the file
        $stubContent = $this->file->get($fileOrigin);

        if($this->option('file_name') != null)
        {
            // Get the fillables
            $validationArray = $this->generateValidation($this->option('file_name'));
            $uploadLogic = $this->checkForFileFields($this->option('file_name'), $modelNameSmall);
            $implodeArrays = $this->implodeArrayFields($this->option('file_name'));
        }
        else {
            $validationArray = null;
            $uploadLogic = null;
            $implodeArrays = null;
        }

        // replace the variables
        $stubContent = str_replace('{{ class_name }}', $this->class_name, $stubContent);
        $stubContent = str_replace('{{ namespace }}', 'App\Http\Controllers', $stubContent);
        $stubContent = str_replace('{{ namespacedModel }}', $namespacedModel, $stubContent);
        $stubContent = str_replace('{{ modelVariable }}', 'id', $stubContent);
        $stubContent = str_replace('{{ validation_array }}', $validationArray, $stubContent);
        $stubContent = str_replace('{{ upload_logic }}', $uploadLogic, $stubContent);
        $stubContent = str_replace('{{ model_name_plural }}', Str::plural(Str::snake(trim($this->class_name))), $stubContent);
        $stubContent = str_replace('{{ model_name_small }}', $modelNameSmall, $stubContent);
        $stubContent = str_replace('{{ converted_arrays }}', $implodeArrays, $stubContent);

        // Put the content into the destination directory
        $this->file->put($fileDestination, $stubContent);

        $this->info('Controller created: ' . $fileDestination);
    }

    protected function generateValidation($fileName)
    {
        $modelFile = __DIR__ . '/../Models/'.$fileName.'.json';
        $data = file_get_contents($modelFile);

        $collection = collect(json_decode($data, true));

        $validationArray = "";
        foreach ($collection['fields'] as $field)
        {
            $validationArray .= "\r\n\t\t\t'".$field['name']."' => '".$field['validation']."',";
        }
        return $validationArray;
    }

    protected function implodeArrayFields($fileName)
    {
        $modelFile = __DIR__ . '/../Models/'.$fileName.'.json';
        $data = file_get_contents($modelFile);

        $collection = collect(json_decode($data, true));

        // $request['sizes'] = implode(',', $request->sizes);

        $convertedArrays = "";
        foreach ($collection['fields'] as $field)
        {
            if($field['html-type'] == 'checkbox' || $field['html-type'] == 'radio')
            {
                $convertedArrays .= "\r\n\t\t".'$request["'.$field['name'].'"] = implode(",", $request->'.$field['name'].');';
            }
        }
        return $convertedArrays;
    }

    protected function checkForFileFields($fileName, $modelNameSmall)
    {
        $modelFile = __DIR__ . '/../Models/'.$fileName.'.json';
        $data = file_get_contents($modelFile);

        $collection = collect(json_decode($data, true));

        $fileInputs = "";
        foreach ($collection['fields'] as $field)
        {
            if($field['html-type'] == 'file')
            {
                $fileInputs .= "\r\n".$this->generateFileUploader($field['name'],$modelNameSmall);
            }
        }
        return $fileInputs;
    }

    protected function generateFileUploader($fieldName, $modelNameSmall) {
        $logicStub = __DIR__ . '/../stubs/logic.upload.stub';
        $logicStubContent = $this->file->get($logicStub);
        $logicStubContent = str_replace('{{ field_name }}', $fieldName, $logicStubContent);
        $logicStubContent = str_replace('{{ model_name_small }}', $modelNameSmall, $logicStubContent);
        return $logicStubContent;
    }
}