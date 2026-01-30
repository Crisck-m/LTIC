<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recordatorios de devolución: cada día a las 8:00 (envía al correo del estudiante)
Schedule::command('prestamos:enviar-recordatorios')->dailyAt('08:00');
