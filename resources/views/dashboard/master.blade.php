<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xs font-bold text-[#111111] uppercase tracking-widest leading-tight">
            Console Control Panel
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6 p-6">
        
        <!-- Top Info Header Card (Two-tone theme) -->
        <div class="bg-[#111111] text-[#FAF9F6] border border-[#111111] rounded-md p-6 lg:p-8 relative overflow-hidden">
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-[#1E1E20] border border-[#2D2D30] text-[8px] font-bold text-[#FAF9F6] uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FAF9F6]"></span>
                    Unified Control Console Enabled
                </div>
                <h1 class="text-3xl lg:text-4xl font-normal italic font-serif tracking-tight text-[#FAF9F6]">Dashboard Master Admin</h1>
                <p class="mt-2 text-[#8E8E90] text-xs leading-relaxed max-w-2xl font-light">
                    Kelola seluruh admin penyewa sistem, tentukan limit kapasitas pelanggan yang diperbolehkan, pantau jumlah tim, serta atur akses operasional dari panel kendali terpusat.
                </p>
            </div>
        </div>

        <!-- Search Bar and Info -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-white p-4 border border-[#E5E5E0] rounded-md">
            <form action="{{ route('dashboard') }}" method="GET" class="w-full md:w-96 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama atau email admin..." 
                       class="w-full text-xs px-3 py-2 bg-[#FAF9F6] border border-[#D1D1CB] focus:border-[#111111] focus:ring-0 rounded-md text-[#111111] font-medium transition-all">
                <button type="submit" class="btn-minimal">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('dashboard') }}" class="btn-minimal-secondary flex items-center justify-center shrink-0">
                        Reset
                    </a>
                @endif
            </form>
            
            <a href="{{ route('users.create') }}" class="btn-minimal w-full md:w-auto flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Admin Baru</span>
            </a>
        </div>

        <!-- System Alerts -->
        @if(session('success'))
            <div class="bg-[#EDF3EC] border border-[#EDF3EC] text-[#346538] px-4 py-3 rounded-md flex items-start gap-2">
                <span class="text-xs font-bold text-[#346538] shrink-0 font-mono">[OK]</span>
                <div>
                    <p class="font-bold text-xs text-[#346538] uppercase tracking-wider">Berhasil</p>
                    <p class="text-xs text-[#346538]/90 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Users List Table -->
        <div class="border border-[#E5E5E0] rounded-md overflow-hidden bg-white">
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3">Nama & Profil</th>
                            <th class="px-5 py-3">Alamat Email</th>
                            <th class="px-5 py-3">Hak Akses Role</th>
                            <th class="px-5 py-3 text-center">Jumlah Sub-Staf</th>
                            <th class="px-5 py-3 text-center">Limit Pelanggan</th>
                            <th class="px-5 py-3 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <!-- Custom letter avatar -->
                                        <div class="w-8 h-8 bg-[#FAF9F6] border border-[#E5E5E0] text-[#111111] font-bold rounded-md flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block font-bold text-[#111111] text-xs truncate">{{ $u->name }}</span>
                                            <span class="block text-[8px] text-[#787774] font-mono mt-0.5">ID: #USR-0{{ $u->id }} @if($u->phone) | HP: {{ $u->phone }} @endif</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-[#121212] text-xs font-semibold">{{ $u->email }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider rounded bg-[#FAF9F6] text-[#111111] border border-[#E5E5E0]">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 bg-[#FAF9F6] border border-[#E5E5E0] text-[#111111] font-mono text-[10px] rounded">
                                        {{ $u->sub_users_count ?? 0 }} Tim
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 bg-[#EDF3EC] border border-[#EDF3EC] text-[#346538] font-mono text-[10px] rounded font-bold">
                                        {{ $u->customer_limit ?? 200 }} Pelanggan
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Edit button -->
                                        <a href="{{ route('users.edit', $u->id) }}" class="btn-minimal-secondary px-2 py-1.5" title="Edit Admin">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <!-- Delete form button -->
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin penyewa ini beserta seluruh sub-staf miliknya?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center justify-center p-1.5 bg-[#FDEBEC] hover:bg-[#9F2F2D] hover:text-white text-[#9F2F2D] border border-[#FDEBEC] rounded-md transition-all cursor-pointer" title="Hapus Admin">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-[#787774] font-semibold font-mono text-xs">
                                    [Belum ada admin penyewa terdaftar]
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
