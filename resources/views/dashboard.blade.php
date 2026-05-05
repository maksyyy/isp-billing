<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
    </x-slot>

    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">

        <!-- Card -->
        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-blue-500">
            <h3 class="text-gray-500">Total Pelanggan</h3>
            <p class="text-3xl font-bold text-blue-600">
                {{ $totalCustomers }}
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-green-500">
            <h3 class="text-gray-500">Total Paket</h3>
            <p class="text-3xl font-bold text-green-600">
                {{ $totalPackages }}
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-yellow-500">
            <h3 class="text-gray-500">Total Invoice</h3>
            <p class="text-3xl font-bold text-yellow-600">
                {{ $totalInvoices }}
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-4 border-l-4 border-purple-500">
            <h3 class="text-gray-500">Sudah Dibayar</h3>
            <p class="text-3xl font-bold text-purple-600">
                {{ $paidInvoices }}
            </p>
        </div>

    </div>
</x-app-layout>