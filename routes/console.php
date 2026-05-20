<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// 🔥 Jalankan otomatis setiap tanggal 1 jam 01:00 pagi
Schedule::command('invoices:generate')->monthlyOn(1, '01:00');

Schedule::command('tickets:archive')->dailyAt('00:00');
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
