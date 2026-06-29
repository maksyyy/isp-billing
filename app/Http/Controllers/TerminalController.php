<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Net\SSH2;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TerminalController extends Controller
{
    /**
     * Show SSH Terminal Console Page
     */
    public function index(): View
    {
        abort_unless(auth()->user()->role == 'master', 403);

        $connected = session()->has('ssh_host');
        $host = session('ssh_host');
        $port = session('ssh_port', 22);
        $username = session('ssh_username');
        $cwd = session('ssh_cwd', '~');

        return view('terminal.index', compact('connected', 'host', 'port', 'username', 'cwd'));
    }

    /**
     * Connect to Remote SSH Server
     */
    public function connect(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->role == 'master', 403);

        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $ssh = new SSH2($request->host, $request->port, 5); // 5 seconds connection timeout
            if (!$ssh->login($request->username, $request->password)) {
                return back()->withErrors(['connection' => 'Login gagal: Periksa username dan password Anda.']);
            }

            // Save credentials to session
            session([
                'ssh_host' => $request->host,
                'ssh_port' => $request->port,
                'ssh_username' => $request->username,
                'ssh_password' => $request->password,
                'ssh_cwd' => '~'
            ]);

            return redirect()->route('terminal.index')->with('success', 'Koneksi SSH berhasil terhubung!');
        } catch (\Exception $e) {
            return back()->withErrors(['connection' => 'Gagal terhubung ke server SSH: ' . $e->getMessage()]);
        }
    }

    /**
     * Disconnect SSH Session
     */
    public function disconnect(): RedirectResponse
    {
        abort_unless(auth()->user()->role == 'master', 403);

        session()->forget(['ssh_host', 'ssh_port', 'ssh_username', 'ssh_password', 'ssh_cwd']);

        return redirect()->route('terminal.index')->with('success', 'Sesi SSH telah diputus.');
    }

    /**
     * Execute Command via SSH
     */
    public function execute(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->role == 'master', 403);

        if (!session()->has('ssh_host')) {
            return response()->json(['error' => 'Tidak ada sesi SSH aktif. Silakan hubungkan terlebih dahulu.'], 400);
        }

        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->command;
        $host = session('ssh_host');
        $port = session('ssh_port');
        $username = session('ssh_username');
        $password = session('ssh_password');
        $cwd = session('ssh_cwd', '~');

        // Block dangerous commands or interactive programs
        $lowerCommand = trim(strtolower($command));
        if (in_array($lowerCommand, ['top', 'htop', 'nano', 'vi', 'vim', 'less', 'more', 'screen'])) {
            return response()->json(['output' => "Error: Program interaktif '" . $command . "' tidak didukung oleh web terminal ini.\n"]);
        }

        try {
            $ssh = new SSH2($host, $port, 5);
            if (!$ssh->login($username, $password)) {
                return response()->json(['error' => 'Login SSH gagal, sesi kedaluwarsa.'], 401);
            }

            // Chain command: CD into current directory, execute user command, print unique marker, and print new directory path
            $chainedCommand = "cd " . escapeshellarg($cwd) . " && " . $command . " && echo \"___CWD___\" && pwd";
            
            // Set execution timeout (e.g. 15 seconds) to prevent command from hanging
            $ssh->setTimeout(15);
            $output = $ssh->exec($chainedCommand);

            // Handle command execution timeout or empty output
            if ($ssh->isTimeout()) {
                return response()->json([
                    'output' => $output . "\n[Command timed out after 15 seconds]\n",
                    'cwd' => $cwd
                ]);
            }

            $newCwd = $cwd;
            // Parse output for new working directory path
            if (strpos($output, '___CWD___') !== false) {
                $parts = explode('___CWD___', $output);
                $output = rtrim($parts[0]);
                $newCwd = trim($parts[1] ?? $cwd);
                session(['ssh_cwd' => $newCwd]);
            }

            return response()->json([
                'output' => $output === '' ? '' : $output . "\n",
                'cwd' => $newCwd
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Koneksi error: ' . $e->getMessage()], 500);
        }
    }
}
