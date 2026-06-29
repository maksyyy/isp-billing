<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xs font-bold text-[#111111] uppercase tracking-widest leading-tight">
            Console Control Panel
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6 p-6">
        
        <!-- Top Info Header Card (Light theme) -->
        <div class="bg-[#F4F4F5] text-[#111111] border border-[#E4E4E7] backdrop-blur-md rounded-md p-6 lg:p-8 relative overflow-hidden shadow-sm">
            <div class="absolute -top-12 -right-12 w-56 h-56 bg-[#8B5CF6]/5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-56 h-56 bg-[#6366F1]/5 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-[#FAF9F6] border border-[#E4E4E7] text-[8px] font-bold text-[#111111] uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] animate-pulse"></span>
                    Unified Control Console Enabled
                </div>
                <h1 class="text-3xl lg:text-4xl font-bold tracking-tight text-[#111111]">Dashboard Master Admin</h1>
                <p class="mt-2 text-[#71717A] text-xs leading-relaxed max-w-2xl font-light">
                    Kelola seluruh admin penyewa sistem, tentukan limit kapasitas pelanggan yang diperbolehkan, pantau jumlah tim, serta atur akses operasional dari panel kendali terpusat.
                </p>
            </div>
        </div>

        <!-- STATS CARDS GRID -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Total Tenants -->
            <div class="app-card p-6 flex items-center justify-between relative overflow-hidden bg-white shadow-xs border border-[#E4E4E7]">
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-wider text-[#71717A]">Total Admin Penyewa</p>
                    <h3 class="text-3xl font-extrabold text-[#111111] mt-2 font-heading">{{ $totalTenants }}</h3>
                    <p class="text-[10px] text-[#71717A] mt-1 font-light">Penyewa aktif terdaftar</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-[#6366F1]/10 border border-[#6366F1]/20 flex items-center justify-center text-[#6366F1] shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>

            <!-- Card 2: Allocated Capacity -->
            <div class="app-card p-6 flex items-center justify-between relative overflow-hidden bg-white shadow-xs border border-[#E4E4E7]">
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-wider text-[#71717A]">Total Limit Pelanggan</p>
                    <h3 class="text-3xl font-extrabold text-[#111111] mt-2 font-heading">{{ number_format($totalCapacity) }}</h3>
                    <p class="text-[10px] text-[#71717A] mt-1 font-light">Kapasitas dialokasikan</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-[#8B5CF6]/10 border border-[#8B5CF6]/20 flex items-center justify-center text-[#8B5CF6] shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <!-- Card 3: Total Sub Staff -->
            <div class="app-card p-6 flex items-center justify-between relative overflow-hidden bg-white shadow-xs border border-[#E4E4E7]">
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-wider text-[#71717A]">Total Staf Terdaftar</p>
                    <h3 class="text-3xl font-extrabold text-[#111111] mt-2 font-heading">{{ $totalStaff }}</h3>
                    <p class="text-[10px] text-[#71717A] mt-1 font-light">Sub-staf operasional tim</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-[#EC4899]/10 border border-[#EC4899]/20 flex items-center justify-center text-[#EC4899] shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Search Bar and Action Button -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-white border border-[#E4E4E7] p-4 rounded-md shadow-xs">
            <form action="{{ route('dashboard') }}" method="GET" class="w-full md:w-96 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama atau email admin..." 
                       class="w-full text-xs px-3.5 py-2.5 bg-[#FFFFFF] border border-[#E4E4E7] focus:outline-none focus:ring-1 focus:ring-[#6366F1]/20 focus:border-[#6366F1]/60 rounded-md text-[#111111] font-semibold transition-all shadow-sm">
                <button type="submit" class="btn-minimal shrink-0">
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
            <div class="bg-[#DCFCE7] border border-[#BBF7D0] text-[#15803D] px-4 py-3 rounded-md flex items-start gap-2">
                <span class="text-xs font-bold text-[#15803D] shrink-0 font-mono">[OK]</span>
                <div>
                    <p class="font-bold text-xs text-[#15803D] uppercase tracking-wider">Berhasil</p>
                    <p class="text-xs text-[#15803D]/90 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Users List Table -->
        <div class="app-card overflow-hidden bg-white border border-[#E4E4E7] shadow-xs">
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="p-3 text-left">Nama & Profil</th>
                            <th class="p-3 text-left">Alamat Email</th>
                            <th class="p-3 text-left">Hak Akses Role</th>
                            <th class="p-3 text-center w-40">Jumlah Sub-Staf</th>
                            <th class="p-3 text-center w-48">Limit Pelanggan</th>
                            <th class="p-3 text-center w-28">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <!-- Custom letter avatar -->
                                        <div class="w-8 h-8 bg-[#FAF9F6] border border-[#E4E4E7] text-[#111111] font-bold rounded-md flex items-center justify-center text-xs shrink-0 shadow-sm">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block font-bold text-[#111111] text-xs truncate">{{ $u->name }}</span>
                                            <span class="block text-[8px] text-[#71717A] font-bold mt-0.5">ID: #USR-0{{ $u->id }} @if($u->phone) | HP: {{ $u->phone }} @endif</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-[#111111] text-xs font-semibold">{{ $u->email }}</td>
                                <td class="p-3">
                                    <span class="inline-flex px-2 py-0.5 text-[8px] font-bold uppercase tracking-wider rounded bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 bg-[#F4F4F5] border border-[#E4E4E7] text-[#111111] font-mono text-[10px] rounded">
                                        {{ $u->sub_users_count ?? 0 }} Tim
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 bg-[#DCFCE7] border border-[#BBF7D0] text-[#15803D] font-mono text-[10px] rounded font-bold">
                                        {{ $u->customer_limit ?? 200 }} Pelanggan
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="inline-flex items-center gap-1.5 justify-center">
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
                                            <button class="inline-flex items-center justify-center p-1.5 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded-md transition-all cursor-pointer shadow-sm" title="Hapus Admin">
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
                                <td colspan="6" class="p-8 text-center text-[#71717A] font-semibold font-mono text-xs">
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
