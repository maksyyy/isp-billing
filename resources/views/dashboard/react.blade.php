<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg font-bold text-slate-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <!-- React Mounting Point -->
    <div id="app" data-role="{{ auth()->user()->role }}"></div>

    <!-- React Scripts -->
    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
</x-app-layout>
