<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Process;

class TerminalController extends Controller
{
    /**
     * Show Local Server Terminal Console Page
     */
    public function index(): View
    {
        abort_unless(auth()->user()->role == 'master', 403);

        $connected = true; // Always connected locally
        $host = gethostname();
        $username = get_current_user() ?: 'web-server';
        
        // Initialize cwd to base path if not set in session
        if (!session()->has('local_cwd')) {
            session(['local_cwd' => base_path()]);
        }
        $cwd = session('local_cwd');

        return view('terminal.index', compact('connected', 'host', 'username', 'cwd'));
    }

    /**
     * Execute Command on Local Server
     */
    public function execute(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->role == 'master', 403);

        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->command;
        $cwd = session('local_cwd', base_path());

        // Block dangerous interactive programs or subshells
        $lowerCommand = trim(strtolower($command));
        if (in_array($lowerCommand, ['top', 'htop', 'nano', 'vi', 'vim', 'less', 'more', 'screen', 'cmd', 'powershell', 'bash', 'sh'])) {
            return response()->json(['output' => "Error: Program interaktif/shell '" . $command . "' tidak didukung oleh web terminal ini.\n"]);
        }

        try {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

            // Construct chained command to execute command and output the final directory path
            if ($isWindows) {
                // cmd.exe style chaining
                $chainedCommand = "cd /d " . escapeshellarg($cwd) . " && " . $command . " & echo ___CWD___ & cd";
            } else {
                // bash/sh style chaining
                $chainedCommand = "cd " . escapeshellarg($cwd) . " && " . $command . " ; echo \"___CWD___\" ; pwd";
            }

            // Run process with 15 seconds timeout
            $result = Process::timeout(15)->run($chainedCommand);
            
            // Extract standard output and error output
            $output = $result->output() . $result->errorOutput();

            // Handle timeout
            if ($result->timedOut()) {
                return response()->json([
                    'output' => $output . "\n[Command timed out after 15 seconds]\n",
                    'cwd' => $cwd
                ]);
            }

            $newCwd = $cwd;
            // Parse output for new working directory path
            if (strpos($output, '___CWD___') !== false) {
                $parts = explode('___CWD___', $output);
                
                // Get output part
                $output = rtrim($parts[0]);
                
                // Get CWD part (trim whitespaces/newlines)
                $newCwd = trim($parts[1] ?? $cwd);
                
                // Clean Windows newlines/directory output
                if ($isWindows) {
                    $newCwd = str_replace("\r", "", $newCwd);
                }
                
                // Only update if the directory actually exists
                if (is_dir($newCwd)) {
                    session(['local_cwd' => $newCwd]);
                } else {
                    $newCwd = $cwd;
                }
            }

            return response()->json([
                'output' => $output === '' ? '' : $output . "\n",
                'cwd' => $newCwd
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengeksekusi perintah: ' . $e->getMessage()], 500);
        }
    }
}
