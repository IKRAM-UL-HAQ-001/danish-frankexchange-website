<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Auth;
class DatabaseExportController extends Controller
{
    public function downloadDatabase()
    {
        // Set the name for the SQL file
        $filename = 'database_export_' . date('Y-m-d_H-i-s') . '.sql';

        // Command to export the database
        $username = escapeshellarg(env('DB_USERNAME'));
        $password = env('DB_PASSWORD');
        $host = escapeshellarg(env('DB_HOST'));
        $databaseName = escapeshellarg(env('DB_DATABASE'));

        $mysqldumpPaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe', // Windows XAMPP
            '/usr/bin/mysqldump',                   // Standard Linux
            '/usr/local/bin/mysqldump',             // macOS / Custom Linux
            '/bin/mysqldump',
        ];

        $mysqldump = 'mysqldump';
        foreach ($mysqldumpPaths as $path) {
            if (file_exists($path)) {
                $mysqldump = escapeshellarg($path);
                break;
            }
        }

        $passwordArg = empty($password) ? '' : '--password=' . escapeshellarg($password);
        $command = "{$mysqldump} --user={$username} {$passwordArg} --host={$host} {$databaseName} 2>&1";

        // Execute the command and get the output
        $output = [];
        $returnVar = 0;

        exec($command, $output, $returnVar);

        // Check if the command was successful
        if ($returnVar !== 0) {
            $errorMessage = implode(" ", $output);
            \Illuminate\Support\Facades\Log::error("Database export failed: " . $errorMessage);
            return redirect()->back()->with('error', 'Failed to export database. Error: ' . $errorMessage);
        }

        // Create the SQL file content
        $sqlContent = implode("\n", $output);

        // Return the SQL file as a download
        return Response::make($sqlContent, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
