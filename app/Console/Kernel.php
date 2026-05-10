<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Dotenv\Dotenv;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                $dotenv = Dotenv::createImmutable(base_path());
                $dotenv->load();
                // Set up file name and backup directory
                $fileName = 'ExchangeProject_backup_' . date('Y_m_d_His') . '.sql';
                $backupDir = storage_path('app/backups');
                $backupPath = $backupDir . '/' . $fileName;

                // Ensure the backup directory exists
                if (!file_exists($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }

                // Fetch database credentials
                $db   = env('DB_DATABASE');
                $user = env('DB_USERNAME');
                $pass = env('DB_PASSWORD');
                $host = env('DB_HOST');

                // Validate database credentials
                if (empty($db) || empty($user) || empty($pass) || empty($host)) {
                    throw new Exception('Database credentials are not properly configured in the .env file.');
                }

                // Build the mysqldump command
                $command = sprintf(
                    'mysqldump --user=%s --password=%s --host=%s %s > %s',
                    escapeshellarg($user),
                    escapeshellarg($pass),
                    escapeshellarg($host),
                    escapeshellarg($db),
                    escapeshellarg($backupPath)
                );

                // Execute the command
                exec($command, $output, $returnVar);

                // Check if the command was successful
                if ($returnVar !== 0) {
                    throw new Exception('mysqldump failed. Command: ' . $command . ' Output: ' . implode("\n", $output));
                }

                // Log success
                \Log::info('Database backup successfully created: ' . $backupPath);
            } catch (\Exception $e) {
                // Log the error
                \Log::error('Database backup failed: ' . $e->getMessage());
            }
        })->dailyAt('00:00')
            ->timezone('Asia/Kolkata')
            ->when(function () {
                return now()->dayOfYear % 15 === 0;
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
