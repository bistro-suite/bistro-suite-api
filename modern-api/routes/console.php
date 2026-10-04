<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:brief', function (): void {
    $this->comment('Bistro Suite API foundation');
})->purpose('Show a short description of this API.');
