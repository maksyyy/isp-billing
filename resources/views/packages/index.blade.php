<x-app-layout>
    <div class="p-6">

        <h2 class="text-3xl font-bold tracking-tight text-[#111111] mb-6">Data Paket</h2>

        <!-- Tombol Tambah -->
        <div class="mb-6">
            <a href="{{ route('packages.create') }}" class="btn-minimal">
               + Tambah Paket
            </a>
        </div>

        <!-- TABLE -->
        <div class="app-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th class="p-3 w-16 text-center">No</th>
                            <th class="p-3">Nama</th>
                            <th class="p-3">Harga</th>
                            <th class="p-3">Speed</th>
                            <th class="p-3 text-center w-48">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($packages as $index => $p)
                        <tr>
                            <td class="p-3 text-center font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="p-3 font-semibold text-[#111111]">{{ $p->name }}</td>
                            <td class="p-3 font-mono text-xs text-[#111111]">
                                Rp {{ number_format($p->price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 font-mono text-xs text-[#71717A]">{{ $p->speed }}</td>

                            <td class="p-3 text-center">
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <!-- EDIT -->
                                    <a href="{{ route('packages.edit', $p->id) }}" class="btn-minimal-secondary px-2.5 py-1 text-[10px] font-bold">
                                        Edit
                                    </a>

                                    <!-- DELETE -->
                                    <form action="{{ route('packages.destroy', $p->id) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Yakin mau hapus paket ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center justify-center px-2.5 py-1 btn-minimal-secondary border-[#EF4444]/30 hover:bg-[#EF4444]/10 hover:border-[#EF4444] text-[#EF4444] rounded-md text-[10px] font-bold transition-all cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>