<?php

namespace Ahmadyousefdev\Automs\Commands;

use Ahmadyousefdev\Automs\Commands\Helpers;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateViewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automs:generate_views {class_name? : The name of the class.} {--file_name=}';

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

        if (!Storage::exists($path)) {
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

        // Generate index view
        $this->generateIndexView();
        // Generate show view
        $this->generateShowView();
        // Generate create view
        $this->generateCreateView();
        // Generate edit view
        $this->generateEditView();

        // Add the navigation
        $this->addNavigation();

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
     * add a navigation to navigation-menu.blade.php
     *
     * @return void
     */
    protected function addNavigation()
    {
        // getting the jetstream navigation file
        $navigationFile = base_path('/resources/views/navigation-menu.blade.php');
        // Get the original content of the file
        $navigationFileContent = $this->file->get($navigationFile);
        // generate the navigation for the base url of the model
        $modelNamePlural = Str::plural(Str::snake(trim($this->class_name)));
        $newNavigation =
        "\t\t\t\t\t" .
        '<x-jet-nav-link href="{{ route('
        . "'" . $modelNamePlural . ".index') }}"
        . '" :active="request()->routeIs('
        . "'" . $modelNamePlural . ".index')"
        . '">{{ __('
        . "'" . Str::ucfirst($modelNamePlural) . "') }}</x-jet-nav-link>";
        // Adding the model navigation after the main x-jet-nav-link
        $navigationFileContent = Helpers::insertTextAfter($navigationFileContent, '</x-jet-nav-link>', $newNavigation);

        // Put the new content in the place of the old one
        $this->file->put($navigationFile, $navigationFileContent);
    }
    
    /**
     * generate Index View
     *
     * @return void
     */
    protected function generateIndexView()
    {
        // Set the origin and destination for the Controller class file
        $fileOrigin = __DIR__ . '/../stubs/view_index.stub';
        $modelNamePlural = Str::plural(Str::snake(trim($this->class_name)));
        $modelNameSmall = Str::snake(trim($this->class_name));
        $fileDestination = base_path('/resources/views/' . $modelNamePlural . '/index.blade.php');

        // Check if folder /resources/views/' . $modelNamePlural doesn't exist to create it
        if (!$this->file->exists(base_path('/resources/views/' . $modelNamePlural))) {
            $this->file->makeDirectory(base_path('/resources/views/' . $modelNamePlural));
        }

        if ($this->file->exists($fileDestination)) {
            $this->info('This file already exists: ' . $fileDestination);
            $this->info('Aborting file creation.');
            return false;
        }

        // Get the original string content of the file
        $stubContent = $this->file->get($fileOrigin);

        if ($this->option('file_name') != null) {
            // Get the fillables
            $tableContent = $this->generateIndexTableContent($this->option('file_name'), $modelNameSmall);
            $ths = $tableContent[0];
            $tds = $tableContent[1];
        } else {
            $ths = null;
            $tds = null;
        }

        // replace the variables
        $stubContent = str_replace('{{ title }}', Str::title(Str::plural($this->class_name)), $stubContent);
        $stubContent = str_replace('{{ ths }}', $ths, $stubContent);
        $stubContent = str_replace('{{ tds }}', $tds, $stubContent);
        $stubContent = str_replace('{{ model_name_plural }}', $modelNamePlural, $stubContent);
        $stubContent = str_replace('{{ model_name_small }}', $modelNameSmall, $stubContent);

        // Put the content into the destination directory
        $this->file->put($fileDestination, $stubContent);

        $this->info('View created: ' . $fileDestination);
    }
    
    /**
     * generate Create View
     *
     * @return void
     */
    protected function generateCreateView()
    {
        # We have the main create stub which has the form outer code
        $mainCreateStub = __DIR__ . '/../stubs/view_form.stub';
        $modelNamePlural = Str::plural(Str::snake(trim($this->class_name)));
        $fileDestination = base_path('/resources/views/' . $modelNamePlural . '/create.blade.php');

        if ($this->file->exists($fileDestination)) {
            $this->info('This file already exists: ' . $fileDestination);
            $this->info('Aborting file creation.');
            return false;
        }

        # then we add each fillable from its own stub file to the main create stub
        if ($this->option('file_name') != null) {
            // switch between all fillables types to generate them
            $modelFile = __DIR__ . '/../Models/' . $this->option('file_name') . '.json';
            $data = file_get_contents($modelFile);
            $collection = collect(json_decode($data, true));
            $formElements = '';
            foreach ($collection['fields'] as $field) {
                $formInputStubContent = $this->generateFormInputs($field,false);
                $formElements .= "\r\n".$formInputStubContent;
            }
        } else {
            $formElements = '';
        }

        # then we write the full result to the view folder
        // replace the variables in the main create stub
        $stubContent = $this->file->get($mainCreateStub);
        $stubContent = str_replace('{{ title }}', 'Create ' . Str::title(Str::snake($this->class_name)), $stubContent);
        $stubContent = str_replace('{{ model_name_plural }}', $modelNamePlural, $stubContent);
        $stubContent = str_replace('{{ form_action }}', 'store', $stubContent);
        $stubContent = str_replace('{{ put }}', null, $stubContent);
        $stubContent = str_replace('{{ route_parameter }}', null, $stubContent);
        $stubContent = str_replace('{{ form_elements }}', $formElements, $stubContent);
        // Put the content into the destination directory
        $this->file->put($fileDestination, $stubContent);

        $this->info('View created: ' . $fileDestination);
    }

    protected function generateEditView()
    {
        # We have the main create stub which has the form outer code
        $mainCreateStub = __DIR__ . '/../stubs/view_form.stub';
        $modelNamePlural = Str::plural(Str::snake(trim($this->class_name)));
        $fileDestination = base_path('/resources/views/' . $modelNamePlural . '/edit.blade.php');

        if ($this->file->exists($fileDestination)) {
            $this->info('This file already exists: ' . $fileDestination);
            $this->info('Aborting file creation.');
            return false;
        }

        # then we add each fillable from its own stub file to the main create stub
        if ($this->option('file_name') != null) {
            // switch between all fillables types to generate them
            $modelFile = __DIR__ . '/../Models/' . $this->option('file_name') . '.json';
            $data = file_get_contents($modelFile);
            $collection = collect(json_decode($data, true));
            $formElements = '';
            foreach ($collection['fields'] as $field) {
                $formInputStubContent = $this->generateFormInputs($field,true);
                $formElements .= "\r\n".$formInputStubContent;
            }
        } else {
            $formElements = '';
        }

        # then we write the full result to the view folder
        // replace the variables in the main create stub
        $stubContent = $this->file->get($mainCreateStub);
        $stubContent = str_replace('{{ title }}', 'Edit ' . Str::title(Str::snake(trim($this->class_name))), $stubContent);
        $stubContent = str_replace('{{ model_name_plural }}', $modelNamePlural, $stubContent);
        $stubContent = str_replace('{{ form_action }}', 'update', $stubContent);
        $stubContent = str_replace('{{ put }}', '<input type="hidden" name="_method" value="PUT">', $stubContent);
        $stubContent = str_replace('{{ route_parameter }}', ', $'.Str::snake(trim($this->class_name)).'->id', $stubContent);
        $stubContent = str_replace('{{ form_elements }}', $formElements, $stubContent);
        // Put the content into the destination directory
        $this->file->put($fileDestination, $stubContent);

        $this->info('View created: ' . $fileDestination);
    }
    
    /**
     * generate Show View
     * 
     * For now, we will generate each fillable as a row and a table
     * we will be planning to create a view for each popular model
     *
     * @return void
     */
    protected function generateShowView()
    {
        $mainCreateStub = __DIR__ . '/../stubs/view_show.stub';
        $modelNamePlural = Str::plural(Str::snake(trim($this->class_name)));
        $modelNameSmall = Str::snake(trim($this->class_name));
        $fileDestination = base_path('/resources/views/' . $modelNamePlural . '/show.blade.php');
        $showElements = '';
        if ($this->file->exists($fileDestination)) {
            $this->info('This file already exists: ' . $fileDestination);
            $this->info('Aborting file creation.');
            return false;
        }

        if ($this->option('file_name') != null) {
            // switch between all fillables types to generate them
            $modelFile = __DIR__ . '/../Models/' . $this->option('file_name') . '.json';
            $data = file_get_contents($modelFile);
            $collection = collect(json_decode($data, true));
            
            foreach ($collection['fields'] as $field) {
                $formInputStubContent = $this->generateShowElement($field);
                $showElements .= "\r\n".$formInputStubContent;
            }
        }

        # then we write the full result to the view folder
        // replace the variables in the main create stub
        $stubContent = $this->file->get($mainCreateStub);
        $stubContent = str_replace('{{ title }}', 'Show ' . Str::title(Str::snake($this->class_name)), $stubContent);
        $stubContent = str_replace('{{ model_name_plural }}', $modelNamePlural, $stubContent);
        $stubContent = str_replace('{{ model_name_small }}', $modelNameSmall, $stubContent);
        $stubContent = str_replace('{{ show_elements }}', $showElements, $stubContent);
        // Put the content into the destination directory
        $this->file->put($fileDestination, $stubContent);

        $this->info('View created: ' . $fileDestination);
    }

    /**
     * generate thead ths and tds
     *
     * @param  mixed $file_name
     * @return void
     */
    protected function generateIndexTableContent($file_name, $modelNameSmall)
    {
        $modelFile = __DIR__ . '/../Models/' . $file_name . '.json';
        $data = file_get_contents($modelFile);

        $collection = collect(json_decode($data, true));
        //dd($collection);
        $ths = "";
        $tds = "";
        foreach ($collection['fields'] as $field) {
            if ($field['index'] == true) {
                // this can have its own stub
                $ths .= "\r\n\t\t\t\t\t\t\t" . '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">'
                . "\r\n\t\t\t\t\t\t\t\t" . Str::title($field['name'])
                    . "\r\n\t\t\t\t\t\t\t" . '</th>';
                // if type = image
                if ($field['html-type'] == 'file') {
                    $tds .= "\r\n\t\t\t\t\t\t\t\t" . '<td scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">'
                        . "\r\n\t\t\t\t\t\t\t\t\t" . '<div class="flex-shrink-0 h-10 w-10">'
                        . "\r\n\t\t\t\t\t\t\t\t\t\t" . '<img class="h-10 w-10 rounded-md" src="{{url($' . $modelNameSmall . '->' . $field['name'] . ')}}" alt="">'
                        . "\r\n\t\t\t\t\t\t\t\t\t" . '</div>'
                        . "\r\n\t\t\t\t\t\t\t\t" . '</td>';
                } else {
                    $tds .= "\r\n\t\t\t\t\t\t\t\t" . '<td scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">'
                        . "\r\n\t\t\t\t\t\t\t\t\t{{ $" . $modelNameSmall . '->' . $field['name'] . " }}"
                        . "\r\n\t\t\t\t\t\t\t\t" . '</td>';
                }
            }
        }
        return [$ths, $tds];
    }
    
    /**
     * generate form inputs
     * if edit == true,
     * it will add the old value to {{ old('input_name',$value) }}
     *
     * @param  mixed $field
     * @param  mixed $edit
     * @return void
     */
    protected function generateFormInputs($field, $edit)
    {
        $fieldName = $field['name'];
        $fieldNameTitle = Str::title(str_replace('_', ' ', $field['name']));
        $fieldRequired = $field['nullable'] == false ? 'required' : '';
        $formInputStub = __DIR__ . '/../stubs/form_elements/' . $field['html-type'] . '.stub';
        $formInputStubContent = $this->file->get($formInputStub);
        $formInputStubContent = str_replace('{{ input_name }}', $fieldName, $formInputStubContent);
        $formInputStubContent = str_replace('{{ input_name_title }}', $fieldNameTitle, $formInputStubContent);
        $formInputStubContent = str_replace('{{ required }}', $fieldRequired, $formInputStubContent);
        // if edit form then fill the old values
        $formInputStubContent = str_replace('{{ old_value }}', $edit == true ? ', $'.Str::snake(trim($this->class_name)).'->'.$fieldName : null, $formInputStubContent);
        // number if float
        if ($field['html-type'] == 'number' && $field['data-type'] == 'float') {
            $customVar = 'step="any"';
        }
        else {
            $customVar = null;
        }
        $formInputStubContent = str_replace('{{ custom_var }}', $customVar, $formInputStubContent);
        // checkbox
        if ($field['html-type'] == 'checkbox') {
            $checkboxSingleInput = __DIR__ . '/../stubs/form_elements/checkbox_single_input.stub';
            $checkboxValues = '';
            foreach ($field['values'] as $value) {
                $checkboxSingleInputContent = $this->file->get($checkboxSingleInput);
                $checkboxSingleInputContent = str_replace('{{ value }}', $value, $checkboxSingleInputContent);
                $checkboxSingleInputContent = str_replace('{{ required }}', $fieldRequired, $checkboxSingleInputContent);
                $checkboxSingleInputContent = str_replace('{{ input_name }}', $fieldName, $checkboxSingleInputContent);
                $checkboxSingleInputContent = str_replace('{{ checked }}', $edit == true ? '@if(in_array("'.$value.'",explode(",",$'.Str::snake(trim($this->class_name)).'->'.$fieldName.'))) checked @endif' : null, $checkboxSingleInputContent);
                $checkboxValues .= "\r\n".$checkboxSingleInputContent;
            }
            $formInputStubContent = str_replace('{{ checkbox_inputs }}', $checkboxValues, $formInputStubContent);
        }
        // radio
        if ($field['html-type'] == 'radio') {
            $radioSingleInput = __DIR__ . '/../stubs/form_elements/radio_single_input.stub';
            $radioValues = '';
            foreach ($field['values'] as $value) {
                $radioSingleInputContent = $this->file->get($radioSingleInput);
                $radioSingleInputContent = str_replace('{{ value }}', $value, $radioSingleInputContent);
                $radioSingleInputContent = str_replace('{{ required }}', $fieldRequired, $radioSingleInputContent);
                $radioSingleInputContent = str_replace('{{ input_name }}', $fieldName, $radioSingleInputContent);
                $radioSingleInputContent = str_replace('{{ checked }}', $edit == true ? '@if(in_array("'.$value.'",explode(",",$'.Str::snake(trim($this->class_name)).'->'.$fieldName.'))) checked @endif' : null, $radioSingleInputContent);
                $radioValues .= "\r\n".$radioSingleInputContent;
            }
            $formInputStubContent = str_replace('{{ radio_inputs }}', $radioValues, $formInputStubContent);
        }
        return $formInputStubContent;
    }


    protected function generateShowElement($field)
    {
        $fieldName = $field['name'];
        $fieldNameTitle = Str::title(str_replace('_', ' ', $field['name']));

        $viewElementStub = __DIR__ . '/../stubs/view_show_element.stub';
        $viewElementStubContent = $this->file->get($viewElementStub);
        $viewElementStubContent = str_replace('{{ element_name }}', $fieldNameTitle, $viewElementStubContent);

        // if image
        if ($field['html-type'] == 'file') { // we need to figure out something to verify if it's an image or a file
            $viewElementStubContent = str_replace('{{ element_value }}',
            '<img class="w-60" src="{{Storage::disk("public")->url($'.Str::snake(trim($this->class_name)).'->'.$fieldName.')}}" />',
            $viewElementStubContent);
        }
        else {
            $viewElementStubContent = str_replace('{{ element_value }}',
            '{{ $'.Str::snake(trim($this->class_name)).'->'.$fieldName.' }}',
            $viewElementStubContent);
        }

        return $viewElementStubContent;
    }
    

}
