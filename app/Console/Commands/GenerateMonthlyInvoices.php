<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Invoice;

class GenerateMonthlyInvoices extends Command
{
    // Nama command untuk dijalankan via terminal / scheduler
    protected $signature = 'invoices:generate';

    // Deskripsi command
    protected $description = 'Generate tagihan bulanan otomatis untuk semua pelanggan';

    public function handle()
    {
        $this->components->info('Memulai pembuatan invoice otomatis...');

        // Ambil semua customer yang memiliki paket
        $customers = Customer::with('package')->get();
        
        if ($customers->isEmpty()) {
            $this->components->warn('Tidak ada data pelanggan ditemukan.');
            return;
        }

        $dueDate = now()->addDays(10)->toDateString(); // Jatuh tempo 10 hari dari sekarang

        $count = 0;
        $this->output->progressStart($customers->count());

        foreach ($customers as $customer) {
            if ($customer->package) {
                Invoice::create([
                    'customer_id' => $customer->id,
                    'amount' => $customer->package->price,
                    'due_date' => $dueDate,
                    'status' => 'unpaid',
                    'paid_amount' => 0
                ]);
                $count++;
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();
        $this->components->info("Berhasil membuat {$count} invoice untuk bulan ini.");
    }
}