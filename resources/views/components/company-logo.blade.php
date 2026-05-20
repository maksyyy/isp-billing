@props(['markClass' => 'h-11 w-11', 'textClass' => 'text-xl', 'showText' => true])

@php
    $user = auth()->user();
    $companyName = 'ISP Billing';
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
             class="{{ $markClass }} object-contain rounded-xl bg-white p-1 shadow-sm border border-slate-100">
    @else
        <div class="{{ $markClass }} rounded-xl bg-gradient-to-tr from-indigo-600 to-cyan-500 text-white flex items-center justify-center font-extrabold shadow-md">
            ISP
        </div>
    @endif

    @if($showText)
        <div class="min-w-0">
            <p class="{{ $textClass }} font-bold text-inherit leading-tight truncate">{{ $companyName }}</p>
            <p class="text-[10px] uppercase font-bold tracking-wider opacity-60">Billing & Network</p>
        </div>
    @endif
</div>
