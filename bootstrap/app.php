<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('vencimentos:send-daily')->dailyAt('09:00');
        $schedule->command('vencimentos:send-reminders')->dailyAt('08:00');
        $schedule->command('whatsapp:send-overdue')->dailyAt('10:00');
        $schedule->command('reminders:send')->dailyAt('09:00');
    })
    ->create();
