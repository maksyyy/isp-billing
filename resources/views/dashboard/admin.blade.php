<x-app-layout>
<div class="p-6">

    <h2 class="text-2xl font-bold mb-4">Dashboard Admin</h2>

    <div class="grid grid-cols-3 gap-4">

        <div class="bg-blue-500 text-white p-4 rounded shadow">
            👤 Total Pelanggan
            <div class="text-2xl font-bold mt-2">
                {{ \App\Models\Customer::count() }}
            </div>
        </div>

        <div class="bg-green-500 text-white p-4 rounded shadow">
            💰 Total Invoice
            <div class="text-2xl font-bold mt-2">
                {{ \App\Models\Invoice::count() }}
            </div>
        </div>

        <div class="bg-purple-500 text-white p-4 rounded shadow">
            📦 Paket
            <div class="text-2xl font-bold mt-2">
                {{ \App\Models\Package::count() }}
            </div>
        </div>

    </div>

</div>
</x-app-layout>