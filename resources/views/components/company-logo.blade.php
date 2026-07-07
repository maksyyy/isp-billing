@props(['markClass' => 'h-11 w-11', 'textClass' => 'text-xl', 'showText' => true])

@php
    $user = auth()->user();
    $companyName = 'JARTS ISP MANAJEMENT';
    $logoFile = 'company-logo.png';

    if ($user) {
        // Ambil admin utama (tenant) untuk user ini
        $admin = $user->parent_admin_id ? \App\Models\User::find($user->parent_admin_id) : $user;
        if ($admin) {
            $companyName = $admin->company_name ?? 'ISP Billing';
            $logoFile = 'company-logo-' . $admin->id . '.png';
        }
    }

    $logoPath = public_path($logoFile);
    $hasLogo = file_exists($logoPath);
    
    // Fallback ke logo global jika logo spesifik tenant tidak ditemukan
    if (!$hasLogo) {
        $logoFile = 'company-logo.png';
        $logoPath = public_path($logoFile);
        $hasLogo = file_exists($logoPath);
    }
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    @if($hasLogo)
        <img src="{{ asset($logoFile) }}?v={{ filemtime($logoPath) }}"
             alt="{{ $companyName }}"
             width="40"
             height="40"
             class="{{ $markClass }} object-contain rounded-md bg-white p-1 border border-[#E5E5E0]">
    @else
        <div class="{{ $markClass }} rounded-md bg-[#111111] text-white flex items-center justify-center font-bold text-xs border border-[#2D2D30]">
            ISP
        </div>
    @endif

    @if($showText)
        <div class="min-w-0">
            <p class="{{ $textClass }} font-bold text-inherit leading-tight truncate">{{ $companyName }}</p>
            <p class="text-[9px] uppercase font-bold tracking-wider opacity-60">Control Console</p>
        </div>
    @endif
</div>
