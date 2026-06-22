<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-extrabold text-slate-800 leading-tight">
            👑 Master Control Panel
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6 p-6">
        
        <!-- Top Info Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 lg:p-8 shadow-xl relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-56 h-56 bg-indigo-500/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-12 -left-12 w-56 h-56 bg-cyan-500/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-xs font-semibold text-cyan-400 mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                    Unified Control Console Enabled
                </div>
                <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Dashboard Master Admin</h1>
                <p class="mt-2 text-slate-300 text-sm leading-relaxed">
                    Kelola seluruh admin penyewa sistem, tentukan limit kapasitas pelanggan yang diperbolehkan, pantau jumlah tim, serta atur akses operasional dari panel kendali terpusat.
                </p>
            </div>
        </div>

        <!-- Search Bar and Info -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-white p-4 border border-slate-200/80 rounded-2xl shadow-sm">
            <form action="{{ route('dashboard') }}" method="GET" class="w-full md:w-96 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama atau email admin..." 
                       class="w-full text-xs px-4 py-2.5 bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500/50 rounded-xl text-slate-800 font-semibold transition-all">
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('dashboard') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-wider transition-all flex items-center shrink-0">
                        Reset
                    </a>
                @endif
            </form>
            
            <a href="{{ route('users.create') }}" class="w-full md:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-indigo-600/10 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Admin Baru</span>
            </a>
        </div>

        <!-- System Alerts -->
        @if(session('success'))
            <div class="bg-emerald-500/5 border border-emerald-500/20 text-emerald-700 px-5 py-4 rounded-2xl flex items-start gap-3 shadow-sm">
                <span class="text-lg bg-emerald-500/10 text-emerald-600 w-7 h-7 rounded-xl flex items-center justify-center font-bold shrink-0">✓</span>
                <div>
                    <p class="font-bold text-sm text-emerald-800">Berhasil</p>
                    <p class="text-xs text-emerald-700/80 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Users List Table -->
        <div class="border border-slate-200/80 rounded-2xl overflow-hidden bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200/80">
                            <th class="px-5 py-3.5">Nama & Profil</th>
                            <th class="px-5 py-3.5">Alamat Email</th>
                            <th class="px-5 py-3.5">Hak Akses Role</th>
                            <th class="px-5 py-3.5 text-center">Jumlah Sub-Staf</th>
                            <th class="px-5 py-3.5 text-center">Limit Pelanggan</th>
                            <th class="px-5 py-3.5 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <!-- Custom letter avatar -->
                                        <div class="w-9 h-9 bg-indigo-100 text-indigo-700 font-bold rounded-xl flex items-center justify-center text-sm shadow-sm shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="block font-bold text-slate-800 text-sm truncate">{{ $u->name }}</span>
                                            <span class="block text-[10px] text-slate-400 font-semibold mt-0.5">ID: #USR-0{{ $u->id }} @if($u->phone) | HP: {{ $u->phone }} @endif</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600 text-sm font-semibold">{{ $u->email }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border bg-indigo-500/5 text-indigo-600 border-indigo-500/10">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-600 font-bold text-xs rounded-md">
                                        {{ $u->sub_users_count ?? 0 }} Tim
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex px-2.5 py-1 text-[11px] font-extrabold bg-blue-50 border border-blue-200 text-blue-700 rounded-lg">
                                        {{ $u->customer_limit ?? 200 }} Pelanggan
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Edit button -->
                                        <a href="{{ route('users.edit', $u->id) }}" class="p-2 border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 text-slate-500 hover:text-slate-700 rounded-xl transition-all" title="Edit Admin">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <!-- Delete form button -->
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin penyewa ini beserta seluruh sub-staf miliknya?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="p-2 border border-rose-100 hover:border-rose-300 bg-white hover:bg-rose-50 text-rose-500 hover:text-rose-600 rounded-xl transition-all cursor-pointer" title="Hapus Admin">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400 font-semibold">
                                    <div class="text-3xl mb-2">👥</div>
                                    Belum ada admin penyewa terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
