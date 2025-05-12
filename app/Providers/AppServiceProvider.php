<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->bindRepositories();
    }

    # Dynamic  binding for repositories
    private function bindRepositories(): void
    {
        $repositoriesPath = app_path('Repositories');
        $interfacesPath = app_path('Repositories/Interfaces');

        if (!File::isDirectory($repositoriesPath)) {
            return;
        }

        $repositoryFiles = File::files($repositoriesPath);

        foreach ($repositoryFiles as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);

            if (File::isDirectory($file) || strpos($filename, 'Interface') !== false) {
                continue;
            }

            $interfaceName = $filename . 'Interface';
            $interfacePath = $interfacesPath . DIRECTORY_SEPARATOR . $interfaceName . '.php';

            if (File::exists($interfacePath)) {
                $interface = 'App\Repositories\Interfaces\\' . $interfaceName;
                $repository = 'App\Repositories\\' . $filename;

                $this->app->bind($interface, $repository);
            }
        }
    }

    public function boot(): void
    {
        //
    }
}
