<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = \Illuminate\Support\Facades\DB::table('users')->get();

        foreach ($users as $user) {
            $updatedData = [];

            $rawPrtg = $user->prtg_password;
            $rawMikrotik = $user->mikrotik_password;
            $rawTelegram = $user->telegram_bot_token;

            if ($rawPrtg && !$this->isEncrypted($rawPrtg)) {
                $updatedData['prtg_password'] = Crypt::encryptString($rawPrtg);
            }

            if ($rawMikrotik && !$this->isEncrypted($rawMikrotik)) {
                $updatedData['mikrotik_password'] = Crypt::encryptString($rawMikrotik);
            }

            if ($rawTelegram && !$this->isEncrypted($rawTelegram)) {
                $updatedData['telegram_bot_token'] = Crypt::encryptString($rawTelegram);
            }

            if (!empty($updatedData)) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update($updatedData);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $users = \Illuminate\Support\Facades\DB::table('users')->get();

        foreach ($users as $user) {
            $updatedData = [];

            $rawPrtg = $user->prtg_password;
            $rawMikrotik = $user->mikrotik_password;
            $rawTelegram = $user->telegram_bot_token;

            if ($rawPrtg && $this->isEncrypted($rawPrtg)) {
                try {
                    $updatedData['prtg_password'] = Crypt::decrypt($rawPrtg);
                } catch (\Exception $e) {
                    try {
                        $updatedData['prtg_password'] = Crypt::decryptString($rawPrtg);
                    } catch (\Exception $ex) {}
                }
            }

            if ($rawMikrotik && $this->isEncrypted($rawMikrotik)) {
                try {
                    $updatedData['mikrotik_password'] = Crypt::decrypt($rawMikrotik);
                } catch (\Exception $e) {
                    try {
                        $updatedData['mikrotik_password'] = Crypt::decryptString($rawMikrotik);
                    } catch (\Exception $ex) {}
                }
            }

            if ($rawTelegram && $this->isEncrypted($rawTelegram)) {
                try {
                    $updatedData['telegram_bot_token'] = Crypt::decrypt($rawTelegram);
                } catch (\Exception $e) {
                    try {
                        $updatedData['telegram_bot_token'] = Crypt::decryptString($rawTelegram);
                    } catch (\Exception $ex) {}
                }
            }

            if (!empty($updatedData)) {
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update($updatedData);
            }
        }
    }

    private function isEncrypted($value): bool
    {
        try {
            Crypt::decrypt($value);
            return true;
        } catch (DecryptException $e) {
            try {
                Crypt::decryptString($value);
                return true;
            } catch (DecryptException $e) {
                return false;
            }
        }
    }
};
