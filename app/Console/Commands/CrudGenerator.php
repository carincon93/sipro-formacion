<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use File;

class CrudGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {className : Class name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create crud resources';

    protected $className;
    protected $folderName;
    protected $routeName;

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
        $this->gatherParameters();
    }

    protected function gatherParameters()
    {
        $this->className            = $this->argument('className');
        $this->folderName           = $this->ask('What is the folder name (in capitalize and plural)?');
        $this->routeName            = $this->ask('What is the name for the route (in lowercase and plural)? Eg: academic-programs');

        Artisan::call("make:model $this->className -c -r");
        Artisan::call("make:request $this->className"."Request");
        Artisan::call("make:policy $this->className"."Policy --model=".$this->className);
        Artisan::call("make:resource $this->className"."Resource");
        Artisan::call("make:controller API/$this->className"."Controller --api --model=".$this->className);

        File::makeDirectory(base_path()."/resources/js/Pages/{$this->folderName}", 0777, true);
        File::put(base_path()."/resources/js/Pages/{$this->folderName}/Index.svelte", "<script>import { inertia } from '@inertiajs/inertia-svelte'</script>");
        File::put(base_path()."/resources/js/Pages/{$this->folderName}/Show.svelte", "<script>import { inertia } from '@inertiajs/inertia-svelte'</script>");
        File::put(base_path()."/resources/js/Pages/{$this->folderName}/Edit.svelte", "<script>import { inertia } from '@inertiajs/inertia-svelte'</script>");
        File::put(base_path()."/resources/js/Pages/{$this->folderName}/Create.svelte", "<script>import { inertia } from '@inertiajs/inertia-svelte'</script>");
        $this->info(__('Copy and paste the following code in the return view App\Http\Controllers\:controllerName : :folderName', ['controllerName' => $this->className.'Controller;', 'folderName' => $this->folderName]));
        $this->info(__('Copy and paste the following code in the App\Policies\:classNamePolicy : :routeName', ['classNamePolicy' => $this->className.'Policy', 'routeName' => $this->routeName]));
        $this->info(__("Copy and paste the following code in AuthServiceProvider.php: 'App\Models\:className' => 'App\Policies\:classNamePolicy',", ['classNamePolicy' => $this->className.'Policy', 'className' => $this->className]));
        $this->info('Copy and paste the following code in web.php:');
        $this->info(__('use App\Http\Controllers\:controllerName', ['controllerName' => $this->className.'Controller;']));
        $this->info(__('use App\Http\Controllers\API\:controllerName as :controllerAPIName', ['controllerName' => $this->className.'Controller', 'controllerAPIName' => $this->className.'APIController;']));
        $this->info("Route::resource('/{$this->folderName}', {$this->className}Controller::class);");
        $this->info("Route::apiResources(['api/$this->folderName' => {$this->className}Controller::class]);");
        $this->info('Add this routes in the permissions table:');
        $this->info("{$this->routeName}.index");
        $this->info("{$this->routeName}.show");
        $this->info("{$this->routeName}.create");
        $this->info("{$this->routeName}.edit");
        $this->info("{$this->routeName}.delete");
        $this->info('Execute the following comand: php artisan cache:clear');
    }
}
