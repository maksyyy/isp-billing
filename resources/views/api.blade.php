<x-app-layout>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">API Dashboard</h1>

        <!-- TEMPAT REACT -->
        <div id="app"></div>
    </div>

    <!-- ✅ React hanya di sini -->
    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
</x-app-layout>