<x-app-layout>
<div class="p-6">

    <h2 class="text-3xl font-bold tracking-tight text-[#111111] mb-6">Data Tagihan</h2>

    @php
        $role = auth()->user()->role;
    @endphp

    <!-- ACTION BUTTONS -->
    <div class="mb-6 flex gap-2 items-center">
        @if(in_array($role, ['admin','finance']))
            <a href="{{ route('invoices.generate.form') }}" class="btn-minimal">
               Generate Tagihan
            </a>

            <a href="{{ route('invoices.printAll') }}" class="btn-minimal-secondary">
               Cetak Semua
            </a>
        @endif
    </div>

    <!-- TABLE -->
    <div class="app-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="app-table">
                <thead>
                    <tr>
                        @if(in_array($role, ['admin','finance']))
                        <th class="p-3 text-center w-12">
                            <input type="checkbox" onclick="toggleAll(this)" class="rounded border-[#E4E4E7] text-[#6366F1] bg-[#FFFFFF] focus:ring-0">
                        </th>
                        @endif

                        <th class="p-3 text-left">Customer</th>
                        <th class="p-3 text-left">Jumlah</th>
                        <th class="p-3 text-left">Dibayar</th>
                        <th class="p-3 text-left">Jatuh Tempo</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-center w-[320px]">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($invoices as $i)
                    <tr>
                        @if(in_array($role, ['admin','finance']))
                        <td class="p-3 text-center">
                            <input type="checkbox"
                                   name="invoice_ids[]"
                                   value="{{ $i->id }}"
                                   form="bulkForm"
                                   class="rounded border-[#E4E4E7] text-[#6366F1] bg-[#FFFFFF] focus:ring-0">
                        </td>
                        @endif

                        <td class="p-3 font-semibold text-[#111111]">
                            {{ $i->customer->name }}
                        </td>

                        <td class="p-3 font-mono text-xs text-[#111111]">
                            Rp {{ number_format($i->amount) }}
                        </td>

                        <td class="p-3 font-mono text-xs text-[#71717A]">
                            Rp {{ number_format($i->paid_amount ?? 0) }}
                        </td>

                        <td class="p-3 text-xs text-[#71717A] font-mono">
                            {{ $i->due_date }}
                        </td>

                        <td class="p-3 text-xs">
                            @if($i->status == 'paid')
                                <span class="inline-flex px-2 py-0.5 bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0] rounded text-[10px] font-bold uppercase tracking-wider">Lunas</span>
                            @elseif($i->paid_amount > 0)
                                <span class="inline-flex px-2 py-0.5 bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A] rounded text-[10px] font-bold uppercase tracking-wider">Cicilan</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 bg-[#FEE2E2] text-[#B91C1C] border border-[#FECACA] rounded text-[10px] font-bold uppercase tracking-wider">Belum Bayar</span>
                            @endif
                        </td>

                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-1.5 justify-center flex-wrap">
                                <!-- BAYAR -->
                                @if($i->status == 'unpaid')
                                <form action="{{ route('invoices.pay', $i->id) }}" method="POST" class="inline-flex gap-1 items-center">
                                    @csrf
                                    <input type="number" name="amount"
                                           class="border border-[#E4E4E7] px-2 py-1 text-xs w-20 rounded bg-[#FFFFFF] focus:border-[#6366F1]/40 focus:ring-1 focus:ring-[#6366F1]/20 text-xs font-mono text-[#111111] shadow-sm"
                                           placeholder="Nominal" required>
                                    <button class="btn-minimal px-2 py-1 text-[10px]">
                                        Bayar
                                    </button>
                                </form>
                                @endif

                                <!-- SELESAI -->
                                @if($i->status == 'unpaid' && in_array($role, ['admin','finance']))
                                <form action="{{ route('invoices.selesai', $i->id) }}" method="POST"
                                       onsubmit="return confirm('Tandai selesai?')" class="inline">
                                    @csrf
                                    <button class="btn-minimal px-2.5 py-1 text-[10px]">
                                        Selesai
                                    </button>
                                </form>
                                @endif

                                <!-- CETAK -->
                                @if(in_array($role, ['admin','finance']))
                                <a href="{{ route('invoices.print', $i->id) }}" class="btn-minimal-secondary px-2.5 py-1 text-[10px]">
                                    Cetak
                                </a>

                                <form action="{{ route('invoices.destroy', $i->id) }}" method="POST"
                                       onsubmit="return confirm('Yakin ingin menghapus invoice ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center justify-center px-2.5 py-1 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded-md text-[10px] font-bold transition-all cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center p-8 text-[#71717A] font-mono text-xs">
                            [Belum ada data invoice]
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $invoices->links() }}
    </div>

    <!-- BULK ACTIONS -->
    @if(in_array($role, ['admin','finance']))
    <form id="bulkForm" method="POST" class="inline-flex gap-2 mt-6">
        @csrf

        <button formaction="{{ route('invoices.print.selected') }}"
                formnovalidate
                class="btn-minimal-secondary">
            Cetak Terpilih
        </button>

        <button formaction="{{ route('invoices.destroy.selected') }}"
                onclick="return confirm('Yakin ingin menghapus invoice yang terpilih?')"
                class="inline-flex items-center justify-center px-4 py-2.5 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded-md text-xs font-semibold uppercase tracking-wider transition-all duration-150 cursor-pointer">
            Hapus Terpilih
        </button>
    </form>
    @endif

</div>

<script>
function toggleAll(source) {
    let checkboxes = document.querySelectorAll('input[name="invoice_ids[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>

</x-app-layout>
