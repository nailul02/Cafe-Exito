<?php

// Perintah artisan kustom proyek dapat didaftarkan di sini.
use Illuminate\Support\Facades\Schedule;
Schedule::command('refunds:notify-deadlines')->everyTenMinutes()->withoutOverlapping();
