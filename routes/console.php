<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
* This file is used to define console commands for a Laravel application.
* Console commands are used to perform various tasks from the command line, such as running scheduled tasks or performing maintenance tasks.
* In this file, we can define custom commands that can be executed using the `php artisan` command in the terminal.   
* The `Artisan::command` method is used to define a new console command.
* In this example, we are defining a command called `inspire` that will display an inspiring quote when executed.
* The `purpose` method is used to provide a description of what the command does, which can be helpful when listing available commands using `php artisan list`.   
*/
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
