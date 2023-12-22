<?php

namespace Shafiulnaeem\CrudOperation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class CreateCrud extends Command
{
    protected $signature = 'add:crud {name} {columns}';

    protected $description = 'generate crud.';

    public function handle()
    {
        $resource = $this->argument('name');
        $columns = $this->argument('columns');
        $this->info("Generating crud for $resource ...");
        $this->newLine();

        if ($this->createMVC($resource,$columns)){
            $this->info("CRUD generated for $resource successfully.");
        }
    }

    private function createMVC($name,$columns)
    {
        if (config('package.crud', true)) {
            $table = preg_replace('/(?<!^)([A-Z])/', '_$1', $name);
            $table =  strtolower($table).'s';
            $model = base_path('App/Models/'.ucfirst($name).'.php');
            $controller = base_path('App/Http/Controllers/'.ucfirst($name).'Controller.php');
            $request = base_path('App/Http/Requests/'.ucfirst($name).'Request.php');

            if ($this->validateMVC($name,$columns,$table,$model,$controller,$request)){
                // Generate model in the project directory
                $this->info("create model... ");
                $this->createModel($name,$table,$model);

                // generated db
                $this->info("create database... ");
                $this->createMigration($columns,$table);

                // generate request file
                $this->info("create request... ");
                $this->createRequest($name,$columns,$table);

                // generate controller
                $this->info("create controller... ");
                $this->createController($name);
                // view
                $this->info("create view... ");
                $this->createView($name);

                // generate route file
                $this->info("create route... ");
                $this-> createRoute($name);
                // resources folder
                $this->newLine();
                return true;
            }
            return false;

        }
    }

    private function validateMVC($name,$columns,$table,$model,$controller,$request)
    {
        if (file_exists($model)) {
            $this->warn("Model already exists!.try another");
            $this->newLine();
            return false;
        }
        elseif(Schema::hasTable($table)) {
            $this->warn("Table already exists!.try another");
            $this->newLine();
            return false;
        }
        elseif(file_exists($controller)) {
            $this->warn("Controller already exists!.try another");
            $this->newLine();
            return false;
        }
        elseif(file_exists($request)) {
            $this->warn("Request already exists!.try another");
            $this->newLine();
            return false;
        }
        elseif(View::exists(strtolower($name).'.index')) {
            $this->warn("index view file already exists!.try another");
            $this->newLine();
            return false;
        }
        elseif(View::exists(strtolower($name).'.create')) {
            $this->warn("create view file already exists!.try another");
            $this->newLine();
            return false;
        }
        elseif(View::exists(strtolower($name).'.show')) {
            $this->warn("show view file already exists!.try another");
            $this->newLine();
            return false;
        }
        elseif(View::exists(strtolower($name).'.edit')) {
            $this->warn("edit view file already exists!.try another");
            $this->newLine();
            return false;
        }
        return true;
    }

    private function createModel($name,$table,$path)
    {
        Artisan::call('make:model', [
            'name' => ucfirst($name),
            '--force' => true,
        ]);
        // Modify the model file content to include 'protected $guarded = [];'
        return $this->addGuardedProperty(ucfirst($name),$table,$path);
    }
    private function createView($name)
    {
        Artisan::call('make:view', [
            'name' => strtolower($name).'\index',
        ]);
        Artisan::call('make:view', [
            'name' => strtolower($name).'\create',
        ]);
        Artisan::call('make:view', [
            'name' => strtolower($name).'\show',
        ]);
        Artisan::call('make:view', [
            'name' => strtolower($name).'\edit',
        ]);
        $this->info("View file created successfully.");

    }
    private function createRoute($name)
    {
        $resource = strtolower($name);
        $controller = '\\App\Http\Controllers\\'.ucfirst($name).'Controller';
        $routes = "Route::resource('{$resource}',{$controller}::class);";
        // Append the generated routes to the web.php file
        $webPhpFilePath = base_path('routes/web.php');
        file_put_contents($webPhpFilePath, $routes, FILE_APPEND);
        $this->info("Route file created successfully.");

    }

    private function addGuardedProperty($modelName,$table, $path)
    {
        $content = file_get_contents($path);

        // Check if the 'use HasFactory;' statement exists
        if (!Str::contains($content, 'use HasFactory;')) {
            $this->newLine();
            $this->warn("The 'use HasFactory;' statement is missing in the model: {$modelName}");
            return false;
        }

        // Check if the 'protected $guarded' property already exists
        if (Str::contains($content, 'protected $guarded')) {
            $this->newLine();
            $this->warn("The 'protected guarded' property already exists in the model: {$modelName}");
            return false;
        }

        if (Str::contains($content, 'protected $table')) {
            $this->newLine();
            $this->warn("The 'protected table' property already exists in the model: {$modelName}");
            return false;
        }

        // Add 'protected $guarded = [];'
        $guardedProperty = '    protected $guarded = [];';
        $tableProperty = '    protected $table = '."'".$table."';";
        $content = str_replace('use HasFactory;', "use HasFactory;\n\n{$tableProperty}\n\n{$guardedProperty}", $content);

        // Save the modified content back to the file
        file_put_contents($path, $content);
        $this->info("Created  model: {$modelName} successfully.");
        return true;
    }

    private function processColumns($columns,$table){
        $columns = explode(',',$columns);
        $prepareColumns = '     ';
        $rules = '';

        $dataType = dataType();
        $validationRule = validationRule();
        foreach ($columns as $key=> $column){
            $array = explode('-',$column);
            $col_name = trim($array[0]);
            $type = isset($array[1]) ? trim( $array[1]) : 'string';
            $rule = isset($array[2]) ? $array[2] : 'nullable';
            $type = array_key_exists($type,dataType()) ? $dataType[$type] : 'string';
            $rule =  trim($rule) ? trim($rule) : 'nullable';
            if ($key == 0){
                $rules = $rules."'".$col_name."'=>'".$rule."',\n";
            }else{
                $rules = $rules."            '".$col_name."'=>'".$rule."',\n";
            }
            $tableCol = '$table->'.$type."('".$col_name."')->nullable();\n";
            $prepareColumns = $prepareColumns.$tableCol;
        }
        return [
            'column' => $prepareColumns,
            'rules' => $rules
        ];
    }

    private function createMigration($columns,$table)
    {
        $migrationName = "create_{$table}_table";

        // Generate migration
        Artisan::call('make:migration', [
            'name' => $migrationName,
        ]);

        // Modify the migration file content to add columns
        $this->addColumnsToMigration($columns,$table,$migrationName);
        $this->info("Migrating database... ");
        Artisan::call('migrate');
        $this->info("Migrated database successfully. ");
    }

    private function addColumnsToMigration($columns,$table,$migrationName)
    {
        $migrationFilePath = database_path('migrations') . '/' . date('Y_m_d_His') . "_{$migrationName}.php";
        $content = file_get_contents($migrationFilePath);
        $columnsData= $this->processColumns($columns,$table);
        $columnsToAdd = $columnsData['column'];
        $content = Str::replaceFirst('}', "{$columnsToAdd}}", $content);
        // Save the modified content back to the file
        file_put_contents($migrationFilePath, $content);
        $this->info("Added migration for {$migrationName}");
    }

    private function createRequest($name,$columns,$table){
        $name = ucfirst($name);
        // Generate request
        Artisan::call('make:request', [
            'name' => "{$name}Request",
        ]);

        // Modify the request file content to add validation rules and set authorization to true
        $this->customizeRequestFile($name,$columns,$table);
    }
    private function customizeRequestFile($name,$columns,$table)
    {
        $requestFilePath = $this->getRequestFilePath($name);
        $content = file_get_contents($requestFilePath);

        // Add your desired validation rules and set authorization to true
        $rulesData = $this->processColumns($columns,$table);
        $rulesToAdd = $rulesData['rules'];
        // Set authorization to true
        $content = preg_replace(
            '/public function authorize\(\): bool\s*{[^}]+}/s',
            'public function authorize(): bool'."\n    {\n        return true;\n    }",
            $content
        );

        // Add your desired validation rules
        $content = preg_replace(
            '/public function rules\(\): array\s*{[^}]+}/s',
            "public function rules(): array\n    {\n        return [\n            {$rulesToAdd}\n        ];\n    }",
            $content
        );

        // Save the modified content back to the file
        file_put_contents($requestFilePath, $content);

        $this->info("Request file created for {$name} successfully.");
    }
    private function getRequestFilePath($name)
    {
        $requestFileName = "{$name}Request.php";
        return app_path("Http/Requests/{$requestFileName}");
    }

    private function createController($name){
        $name = ucfirst($name);
        // Generate controller
        Artisan::call('make:controller', [
            'name' => "{$name}Controller",
        ]);

        // Modify the controller file content
        $this->customizeControllerFile($name);
    }
    private function functionality($model,$file,$request){

       $function = '
       public function index(){
       '.
        '    $data = \\'.$model.'::orderBy('."'"."id'".",'DESC')".'->paginate(limit(request()->all()));
        '.
        '    return view('."'".$file.".index'".', $data);
        }
 
        public function create(){
        '.
        '    return view('."'".$file.".create'".');
        }
        
        public function store(\\'.$request.' $request){
        '.
        '    $data = $request->all();
        ' .
        '    $data = insert_row('."'".$model."'".', $data);
        ' .
        '    return view('."'".$file.".create'".', $data);
        }
        
         public function show($id){
        ' .
           '    $data = \\'.$model.'::find($id);
        ' .
           '    return view('."'".$file.".show'".', $data);
        }
        
        public function edit($id){
        ' .
        '    $data = \\'.$model.'::find($id);
        ' .
        '    return view('."'".$file.".edit'".', $data);
        }
        
        public function update(\\'.$request.' $request,$id){
        ' .
        '    $data = $request->all();
        ' .
        '    $data = update_row('."'".$model."'".', $data,$id);
        ' .
        '    return view('."'".$file.".edit'".', $data);
        }
        
        public function destroy($id){
        ' .
        '    delete_row('."'".$model."'".', $id);
        ' .
        '    return redirect()->back();
        }
        ';

       return $function;
    }

    protected function customizeControllerFile($name)
    {
        $model = 'App\\Models\\' . $name;
        $request = "App\\Http\Requests\\". $name."Request";
        $controllerFilePath = $this->getControllerFilePath($name);
        $content = file_get_contents($controllerFilePath);

        // Your desired controller content
        $customControllerContent = $this->functionality($model,strtolower($name),$request);
        // Replace the placeholder method content
        $content = str_replace('{', "{ \n\n{$customControllerContent}", $content);

        // Save the modified content back to the file
        file_put_contents($controllerFilePath, $content);

        $this->info("Created {$name}Controller successfully.");
    }

    protected function getControllerFilePath($name)
    {
        $controllerFileName = "{$name}Controller.php";
        return app_path("Http/Controllers/{$controllerFileName}");
    }


}
