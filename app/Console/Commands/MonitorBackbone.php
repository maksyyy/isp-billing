<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BackboneDevice;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class MonitorBackbone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:backbone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor backbone devices with rapid pinging and trigger instant Telegram alerts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Backbone monitoring daemon started... Press Ctrl+C to stop.");

        while (true) {
            try {
                $devices = BackboneDevice::all();
                
                if ($devices->isEmpty()) {
                    $this->line("No backbone devices configured. Waiting...");
                    sleep(3);
                    continue;
                }

                $processes = [];
                foreach ($devices as $device) {
                    $ip = $device->ip;
                    
                    // Platform-dependent ping arguments
                    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                        $cmd = ["ping", "-n", "1", "-w", "1000", $ip];
                    } else {
                        $cmd = ["ping", "-c", "1", "-W", "1", $ip];
                    }

                    $process = new Process($cmd);
                    $process->start();
                    
                    $processes[] = [
                        'device' => $device,
                        'process' => $process,
                    ];
                }

                // Wait for all processes to finish running (with a hard timeout of 2 seconds)
                $start = microtime(true);
                while (count(array_filter($processes, fn($p) => $p['process']->isRunning())) > 0) {
                    if (microtime(true) - $start > 2.0) {
                        // Force stop any lingering processes
                        foreach ($processes as $p) {
                            if ($p['process']->isRunning()) {
                                $p['process']->stop();
                            }
                        }
                        break;
                    }
                    usleep(10000); // Sleep 10ms to avoid busy-waiting CPU spikes
                }

                foreach ($processes as $p) {
                    $device = $p['device'];
                    $process = $p['process'];

                    // 1. Check server ping
                    $serverPingSuccess = ($process->getExitCode() === 0);
                    if (strncasecmp(PHP_OS, 'WIN', 3) === 0 && $serverPingSuccess) {
                        $output = $process->getOutput();
                        if (stripos($output, 'unreachable') !== false || stripos($output, 'timed out') !== false) {
                            $serverPingSuccess = false;
                        }
                    }

                    // 2. Check MikroTik ping (if configured)
                    $mikrotikPingSuccess = true;
                    $mikrotikConnected = true;
                    $adminUser = User::find($device->admin_id);

                    if ($adminUser && !empty($adminUser->mikrotik_host)) {
                        $service = app(\App\Services\MikrotikService::class);
                        $mikrotikConnected = $service->connect($adminUser);
                        
                        if ($mikrotikConnected) {
                            try {
                                $reflection = new \ReflectionClass($service);
                                $apiProp = $reflection->getProperty('api');
                                $apiProp->setAccessible(true);
                                $api = $apiProp->getValue($service);
                                
                                $pingRes = $api->comm('/ping', [
                                    'address' => $device->ip,
                                    'count' => 1
                                ]);
                                
                                $received = (int)($pingRes[0]['received'] ?? 0);
                                if ($received === 0 || isset($pingRes['trap'])) {
                                    $mikrotikPingSuccess = false;
                                }
                            } catch (\Exception $e) {
                                Log::error("MonitorBackbone MikroTik Ping Error for {$device->ip}: " . $e->getMessage());
                                $mikrotikPingSuccess = false;
                            } finally {
                                $service->disconnect();
                            }
                        } else {
                            $mikrotikPingSuccess = false;
                        }
                    }

                    $isUp = false;
                    $hasMikrotik = ($adminUser && !empty($adminUser->mikrotik_host));

                    if ($hasMikrotik) {
                        // Jika MikroTik dikonfigurasi, status ditentukan sepenuhnya dari ping MikroTik
                        $isUp = $mikrotikPingSuccess;
                    } else {
                        // Jika tidak ada MikroTik, gunakan ping Server
                        $isUp = $serverPingSuccess;
                    }

                    $newStatus = $isUp ? 'up' : 'down';
                    $oldStatus = $device->status;

                    // Compute failure reason if down
                    $failureReason = null;
                    if (!$isUp) {
                        $reasons = [];
                        if ($hasMikrotik) {
                            if (!$mikrotikConnected) {
                                $reasons[] = "Koneksi API MikroTik gagal/offline";
                            } else {
                                $reasons[] = "Ping dari MikroTik gagal";
                            }
                        } else {
                            $reasons[] = "Ping dari Server gagal";
                        }
                        $failureReason = implode(", ", $reasons);
                    }

                    if ($newStatus !== $oldStatus) {
                        $device->status = $newStatus;
                        $device->last_ping_at = now();

                        if ($newStatus === 'down') {
                            $device->first_failed_at = now();
                            $device->telegram_alert_sent = false;
                        } else {
                            // Transition to 'up'
                            if ($device->telegram_alert_sent) {
                                $this->sendAlertNotification($device, true, null);
                            }
                            $device->first_failed_at = null;
                            $device->telegram_alert_sent = false;
                        }
                        $device->save();

                        $msg = "Device alert: {$device->name} ({$device->ip}) transitioned from {$oldStatus} to {$newStatus}!";
                        if ($failureReason) {
                            $msg .= " Penyebab: {$failureReason}";
                        }
                        $this->warn($msg);
                    } else {
                        // Simply update last ping time
                        $device->last_ping_at = now();

                        if ($newStatus === 'down') {
                            if (!$device->first_failed_at) {
                                $device->first_failed_at = now();
                            }

                            if (!$device->telegram_alert_sent && (now()->timestamp - $device->first_failed_at->timestamp) > 60) {
                                $this->sendAlertNotification($device, false, $failureReason);
                                $device->telegram_alert_sent = true;
                            }
                        }
                        $device->save();
                    }
                }

            } catch (\Exception $e) {
                Log::error("MonitorBackbone error: " . $e->getMessage());
                $this->error("Error: " . $e->getMessage());
            }

            if (app()->environment('testing')) {
                break;
            }

            // Sleep 2 seconds before the next check iteration
            sleep(2);
        }
    }

    /**
     * Send Telegram notification to admin and NOC users scoped to the tenant.
     */
    protected function sendAlertNotification(BackboneDevice $device, bool $isUp, ?string $failureReason = null)
    {
        Log::info("MonitorBackbone: sendAlertNotification triggered for Device ID {$device->id} ({$device->name}), Admin ID: {$device->admin_id}");

        // Get all admin, NOC & teknisi users under this tenant who have telegram_chat_id
        $usersToNotify = User::where(function($q) use ($device) {
                $q->where('id', $device->admin_id)
                  ->orWhere('parent_admin_id', $device->admin_id);
            })
            ->whereIn('role', ['admin', 'noc', 'teknisi'])
            ->whereNotNull('telegram_chat_id')
            ->get();

        Log::info("MonitorBackbone: Found " . $usersToNotify->count() . " users to notify for Device {$device->name}. User IDs: " . implode(', ', $usersToNotify->pluck('id')->toArray()));

        if ($usersToNotify->isEmpty()) {
            return;
        }

        $adminUser = User::find($device->admin_id);
        $botToken = ($adminUser && $adminUser->telegram_bot_token) ? $adminUser->telegram_bot_token : config('services.telegram.bot_token');

        if (!$botToken) {
            Log::warning("Skipped sending Telegram alert: telegram bot token not configured for admin ID {$device->admin_id}");
            return;
        }

        foreach ($usersToNotify as $user) {
            try {
                $emoji = $isUp ? "🟢" : "🔴";
                $statusText = $isUp ? "UP / KEMBALI NORMAL" : "DOWN / TERPUTUS";
                $directUrl = url('/backbone-alerts');

                $message = "🚨 *NOTIFIKASI INSTAN JARTS ISP*\n\n"
                    . "🖥️ *Perangkat:* {$device->name}\n"
                    . "🌐 *IP Address:* {$device->ip}\n"
                    . "📊 *Status:* {$emoji} *{$statusText}*\n";

                if (!$isUp && $failureReason) {
                    $message .= "⚠️ *Penyebab:* {$failureReason}\n";
                }

                $message .= "🕒 *Waktu:* " . now()->setTimezone($user->timezone ?: 'Asia/Jakarta')->format('Y-m-d H:i:s') . "\n\n"
                    . "🔗 [Kelola Alerts & Backbone]({$directUrl})";

                $response = Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $user->telegram_chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);

                if ($response->failed()) {
                    Log::error("Telegram API Error for chat ID {$user->telegram_chat_id} (Status {$response->status()}): " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Failed sending backbone Telegram notification to chat ID {$user->telegram_chat_id}: " . $e->getMessage());
            }
        }
    }
}
