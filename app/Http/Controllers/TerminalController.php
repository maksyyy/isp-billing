<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use phpseclib3\Net\SSH2;

class TerminalController extends Controller
{
    /**
     * Show Web SSH Terminal Page
     */
    public function index(): View
    {
        abort_unless(auth()->user()->role == 'master', 403);

        $connected = session()->has('ssh_host');
        $host = session('ssh_host', '');
        $username = session('ssh_username', '');
        $port = session('ssh_port', 22);
        $cwd = session('ssh_cwd', '~');

        return view('terminal.index', compact('connected', 'host', 'username', 'port', 'cwd'));
    }

    /**
     * Connect to SSH Server and Store Credentials in Session
     */
    public function connect(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->role == 'master', 403);

        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $host = $request->host;
        $port = (int)$request->port;
        $username = $request->username;
        $password = $request->password;

        try {
            // Test connection with 10s timeout
            $ssh = new SSH2($host, $port, 10);
            if (!$ssh->login($username, $password)) {
                return response()->json([
                    'error' => "Autentikasi gagal: Username atau password salah."
                ], 422);
            }

            // Save encrypted credentials to session
            session([
                'ssh_host' => $host,
                'ssh_port' => $port,
                'ssh_username' => $username,
                'ssh_password' => encrypt($password),
                'ssh_cwd' => trim($ssh->exec('pwd')) ?: '~'
            ]);

            return response()->json([
                'success' => true,
                'cwd' => session('ssh_cwd'),
                'username' => $username,
                'host' => $host
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghubungkan ke server SSH: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Execute Command via SSH
     */
    public function execute(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->role == 'master', 403);

        $request->validate([
            'command' => 'required|string',
        ]);

        if (!session()->has('ssh_host')) {
            return response()->json(['error' => 'Tidak terhubung ke server SSH.'], 403);
        }

        $command = $request->command;
        $cwd = session('ssh_cwd', '~');

        try {
            $host = session('ssh_host');
            $port = session('ssh_port');
            $username = session('ssh_username');
            $password = decrypt(session('ssh_password'));

            $ssh = new SSH2($host, $port, 15); // 15s timeout
            if (!$ssh->login($username, $password)) {
                return response()->json(['error' => 'Koneksi SSH terputus atau autentikasi ulang gagal.'], 401);
            }

            // Execute chaining command to perform task and fetch new directory
            $isSudo = false;
            $execCommand = $command;

            if ($username !== 'root' && preg_match('/^sudo\s+/i', trim($command))) {
                $isSudo = true;
                $execCommand = preg_replace('/^sudo\s+/i', '', trim($command));
            }

            if ($isSudo) {
                // If it is a sudo command, wrap the inner chain in a sudo bash shell and pipe the password
                $innerCommand = "cd " . escapeshellarg($cwd) . " && " . $execCommand . " ; echo \"___CWD___\" ; pwd";
                $chainedCommand = "echo " . escapeshellarg($password) . " | sudo -S -p '' bash -c " . escapeshellarg($innerCommand);
            } else {
                // Normal execution
                $chainedCommand = "cd " . escapeshellarg($cwd) . " && " . $command . " ; echo \"___CWD___\" ; pwd";
            }

            $output = $ssh->exec($chainedCommand);

            $newCwd = $cwd;
            // Parse output for new working directory path
            if (strpos($output, '___CWD___') !== false) {
                $parts = explode('___CWD___', $output);
                
                // Get output part
                $output = rtrim($parts[0]);
                
                // Get CWD part and extract the first non-empty line
                $cwdPart = $parts[1] ?? '';
                $lines = explode("\n", str_replace("\r", "", $cwdPart));
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if ($trimmedLine !== '') {
                        $newCwd = $trimmedLine;
                        break;
                    }
                }
                
                session(['ssh_cwd' => $newCwd]);
            }

            return response()->json([
                'output' => $output === '' ? '' : $output . "\n",
                'cwd' => $newCwd
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengeksekusi perintah SSH: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Disconnect and clear SSH session
     */
    public function disconnect(): JsonResponse
    {
        abort_unless(auth()->user()->role == 'master', 403);

        session()->forget(['ssh_host', 'ssh_port', 'ssh_username', 'ssh_password', 'ssh_cwd']);

        return response()->json(['success' => true]);
    }
}
