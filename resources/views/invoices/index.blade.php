<x-app-layout>
<div class="p-6">

    <h2 class="text-xl font-bold mb-4 text-gray-800">Data Tagihan</h2>

    @php
        $role = auth()->user()->role;
    @endphp

    <!-- 🔥 ACTION BUTTON -->
    <div class="mb-4 flex gap-2 items-center">
        @if(in_array($role, ['admin','finance']))
            <a href="{{ route('invoices.generate.form') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
               Generate Tagihan
            </a>

            <a href="{{ route('invoices.printAll') }}"
               class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow">
               Cetak Semua
            </a>
        @endif
    </div>

    <!-- TABLE -->
    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full border text-sm">

            <thead class="bg-gray-200">
                <tr>
                    @if(in_array($role, ['admin','finance']))
                    <th class="p-2 text-center">
                        <input type="checkbox" onclick="toggleAll(this)">
                    </th>
                    @endif

                    <th class="p-2 text-left">Customer</th>
                    <th class="p-2 text-left">Jumlah</th>
                    <th class="p-2 text-left">Dibayar</th>
                    <th class="p-2 text-left">Jatuh Tempo</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($invoices as $i)
                <tr class="border hover:bg-gray-50">

                    @if(in_array($role, ['admin','finance']))
                    <td class="text-center">
                        <input type="checkbox"
                               name="invoice_ids[]"
                               value="{{ $i->id }}"
                               form="bulkForm">
                    </td>
                    @endif

                    <td class="p-2">
                        {{ $i->customer->name }}
                    </td>

                    <td class="p-2">
                        Rp {{ number_format($i->amount) }}
                    </td>

                    <td class="p-2">
                        Rp {{ number_format($i->paid_amount ?? 0) }}
                    </td>

                    <td class="p-2">
                        {{ $i->due_date }}
                    </td>

                    <td class="p-2">
                        @if($i->status == 'paid')
                            <span class="text-green-600 font-bold">Lunas</span>
                        @elseif($i->paid_amount > 0)
                            <span class="text-yellow-600">Cicilan</span>
                        @else
                            <span class="text-red-600">Belum Bayar</span>
                        @endif
                    </td>

                    <td class="p-2 flex gap-2 items-center justify-center">

                        <!-- BAYAR -->
                        @if($i->status == 'unpaid')
                        <form action="{{ route('invoices.pay', $i->id) }}" method="POST" class="flex gap-1">
                            @csrf
                            <input type="number" name="amount"
                                   class="border p-1 w-20 rounded"
                                   placeholder="Nominal" required>

                            <button class="bg-green-600 hover:bg-green-700 text-white px-2 rounded">
                                Bayar
                            </button>
                        </form>
                        @endif

                        <!-- SELESAI -->
                        @if($i->status == 'unpaid' && in_array($role, ['admin','finance']))
                        <form action="{{ route('invoices.selesai', $i->id) }}" method="POST"
                               onsubmit="return confirm('Tandai selesai?')">
                            @csrf
                            <button class="bg-green-800 hover:bg-green-900 text-white px-2 rounded">
                                Selesai
                            </button>
                        </form>
                        @endif

                        <!-- CETAK -->
                        @if(in_array($role, ['admin','finance']))
                        <a href="{{ route('invoices.print', $i->id) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">
                            Cetak
                        </a>

                        <form action="{{ route('invoices.destroy', $i->id) }}" method="POST"
                               onsubmit="return confirm('Yakin ingin menghapus invoice ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded">
                                Hapus
                            </button>
                        </form>
                        @endif

                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center p-4">
                        Belum ada data invoice
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $invoices->links() }}
    </div>

    <!-- BULK ACTIONS -->
    @if(in_array($role, ['admin','finance']))
    <form id="bulkForm" method="POST" class="inline-flex gap-2 mt-4">
        @csrf

        <button formaction="{{ route('invoices.print.selected') }}"
                formnovalidate
                class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded shadow">
            Cetak Terpilih
        </button>

        <button formaction="{{ route('invoices.destroy.selected') }}"
                onclick="return confirm('Yakin ingin menghapus invoice yang terpilih?')"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow">
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
