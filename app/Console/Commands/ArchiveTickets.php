<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;

class ArchiveTickets extends Command
{
    protected $signature = 'tickets:archive';
    protected $description = 'Auto arsip ticket selesai ke riwayat';

    public function handle()
    {
        $tickets = Ticket::where('status', 'done')
            ->whereNull('archived_at')
            ->get();

        foreach ($tickets as $t) {
            $t->archived_at = now();
            $t->save();
        }

        $this->info('Ticket berhasil diarsipkan ke riwayat');
    }
}