<x-app-layout>
<div class="p-6">

    <h2 class="text-xl font-bold mb-4">Edit Paket</h2>

    <form action="{{ route('packages.update', $package->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" value="{{ $package->name }}" class="border p-2 w-full">
        </div>

        <div class="mb-3">
            <label>Harga</label>
            <input type="number" name="price" value="{{ $package->price }}" class="border p-2 w-full">
        </div>

        <div class="mb-3">
            <label>Speed</label>
            <input type="text" name="speed" value="{{ $package->speed }}" class="border p-2 w-full">
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Update
        </button>

    </form>

</div>
</x-app-layout>