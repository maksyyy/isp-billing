<x-app-layout>
<div class="p-6 max-w-4xl mx-auto">
    <h2 class="text-xl font-bold mb-6 text-[#111111]">Generate Tagihan</h2>

    <div class="app-card p-6">
        <form action="{{ route('invoices.generate.multiple') }}" method="POST">
            @csrf

            <!-- TANGGAL -->
            <div class="mb-6 max-w-sm">
                <label class="block text-xs font-bold text-[#71717A] uppercase tracking-wider mb-2">Jatuh Tempo</label>
                <input type="date" name="due_date" required 
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] transition-all shadow-sm">
            </div>

            <!-- TABEL CUSTOMER -->
            <div class="overflow-x-auto mb-6">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="w-12 text-center">
                                <input type="checkbox" onclick="toggleAll(this)" 
                                       class="rounded bg-[#FFFFFF] border-[#E4E4E7] text-[#6366F1] focus:ring-[#6366F1]/20">
                            </th>
                            <th>Nama</th>
                            <th>Email</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($customers as $c)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="customer_ids[]" value="{{ $c->id }}"
                                       class="rounded bg-[#FFFFFF] border-[#E4E4E7] text-[#6366F1] focus:ring-[#6366F1]/20">
                            </td>
                            <td class="font-bold text-[#111111]">{{ $c->name }}</td>
                            <td class="text-[#71717A]">{{ $c->email }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center p-4 text-[#71717A] italic">Tidak ada customer</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- BUTTON -->
            <div class="flex gap-2">
                <button type="submit" class="btn-minimal">
                    Generate Terpilih
                </button>

                <button type="submit" name="generate_all" value="1"
                        class="bg-[#DCFCE7] text-[#15803D] border border-[#BBF7D0] rounded-md text-xs font-semibold uppercase tracking-wider px-4 py-2.5 hover:bg-[#BBF7D0] hover:text-[#15803D] transition-all duration-150 cursor-pointer active:scale-98 shadow-sm">
                    Generate Semua
                </button>
                
                <a href="{{ route('invoices.index') }}" class="btn-minimal-secondary">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAll(source) {
    let checkboxes = document.querySelectorAll('input[name="customer_ids[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>
</x-app-layout>