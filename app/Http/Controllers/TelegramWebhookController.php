<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, $token)
    {
        // Cari admin user yang memiliki token bot ini
        // Karena telegram_bot_token terenkripsi, kita cari di memori dari semua user yang memiliki token
        $adminUser = User::whereNotNull('telegram_bot_token')
            ->get()
            ->first(function ($user) use ($token) {
                return $user->telegram_bot_token === $token;
            });

        if (!$adminUser) {
            // Cek juga bot token global jika admin tidak ketemu
            if ($token !== config('services.telegram.bot_token')) {
                return response()->json(['message' => 'Invalid token'], 404);
            }
            // Gunakan admin pertama sebagai context jika token global yang dipakai
            $adminUser = User::where('role', 'admin')->first(); 
        }

        $update = $request->all();
        
        if (!isset($update['message'])) {
            return response()->json(['status' => 'ignored']);
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';

        if (!$chatId) {
            return response()->json(['status' => 'no-chat-id']);
        }

        // Penanganan Perintah / Command
        if ($text === '/start' || $text === '/help') {
            $companyName = $adminUser ? ($adminUser->company_name ?? 'ISP Billing') : 'ISP Billing';
            $responseText = "👋 Halo! Selamat datang di Bot Notifikasi *{$companyName}*.\n\n"
                . "Gunakan tombol menu di bawah ini untuk berinteraksi:";
            
            $this->sendMessage($token, $chatId, $responseText, [
                'keyboard' => [
                    [['text' => '🆔 Dapatkan Chat ID']],
                    [['text' => 'ℹ️ Info Sistem']]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]);
        } elseif ($text === '🆔 Dapatkan Chat ID' || $text === '/id') {
            $responseText = "🔑 *Chat ID Telegram Anda adalah:*\n\n"
                . "`{$chatId}`\n\n"
                . "Silakan salin ID di atas (ketuk sekali untuk menyalin pada ponsel) dan masukkan ke halaman Profil Anda di sistem ISP Billing.";

            $this->sendMessage($token, $chatId, $responseText);
        } elseif ($text === 'ℹ️ Info Sistem') {
            $companyName = $adminUser ? ($adminUser->company_name ?? 'ISP Billing') : 'ISP Billing';
            $responseText = "🤖 *Bot Notifikasi - {$companyName}*\n\n"
                . "Bot ini digunakan untuk mengirimkan notifikasi tiket gangguan/aduan langsung ke teknisi operasional secara real-time.\n\n"
                . "Status Bot: *Aktif* 🟢";

            $this->sendMessage($token, $chatId, $responseText);
        } else {
            // Default fallback response
            $responseText = "Silakan gunakan tombol menu di bawah ini untuk berinteraksi.";
            $this->sendMessage($token, $chatId, $responseText, [
                'keyboard' => [
                    [['text' => '🆔 Dapatkan Chat ID']],
                    [['text' => 'ℹ️ Info Sistem']]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    private function sendMessage($token, $chatId, $text, $replyMarkup = null)
    {
        try {
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ];

            if ($replyMarkup) {
                $payload['reply_markup'] = json_encode($replyMarkup);
            }

            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);
        } catch (\Exception $e) {
            Log::error("Telegram Webhook Reply Error: " . $e->getMessage());
        }
    }
}
