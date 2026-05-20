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
        $this->components->info('Mengecek tiket yang siap diarsipkan...');

        $tickets = Ticket::where('status', 'done')
            ->whereNull('archived_at')
            ->get();

        if ($tickets->isEmpty()) {
            $this->components->warn('Tidak ada tiket yang perlu diarsipkan saat ini.');
            return;
        }

        $this->output->progressStart($tickets->count());

        foreach ($tickets as $t) {
            $t->archived_at = now();
            $t->save();
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();
        $this->components->info("Berhasil mengarsipkan {$tickets->count()} tiket ke riwayat.");
    }
}