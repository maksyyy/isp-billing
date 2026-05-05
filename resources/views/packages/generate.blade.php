<x-app-layout>
<div class="p-6">

    <h2 class="text-xl font-bold mb-4">Generate Tagihan</h2>

    <form action="{{ route('invoices.generate.multiple') }}" method="POST">
        @csrf

        <!-- TANGGAL -->
        <div class="mb-4">
            <label class="block mb-1">Jatuh Tempo</label>
            <input type="date" name="due_date" required class="border p-2 rounded w-full">
        </div>

        <!-- CHECKLIST CUSTOMER -->
        <table class="w-full border mb-4">
            <thead>
                <tr class="bg-gray-200">
                    <th><input type="checkbox" onclick="toggleAll(this)"></th>
                    <th class="p-2">Nama</th>
                    <th class="p-2">Email</th>
                </tr>
            </thead>

            <tbody>
                @foreach($customers as $c)
                <tr class="border">
                    <td class="text-center">
                        <input type="checkbox" name="customer_ids[]" value="{{ $c->id }}">
                    </td>
                    <td class="p-2">{{ $c->name }}</td>
                    <td class="p-2">{{ $c->email }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- BUTTON -->
        <div class="flex gap-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded">
                Generate Terpilih
            </button>

            <button name="generate_all" value="1"
                    class="bg-green-600 text-white px-4 py-2 rounded">
                Generate Semua
            </button>
        </div>

    </form>

</div>

<script>
function toggleAll(source) {
    let checkboxes = document.querySelectorAll('input[name="customer_ids[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>

</x-app-layout>