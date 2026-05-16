@props(['markClass' => 'h-11 w-11', 'textClass' => 'text-xl', 'showText' => true])

@php
    $logoPath = public_path('company-logo.png');
    $hasLogo = file_exists($logoPath);
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    @if($hasLogo)
        <img src="{{ asset('company-logo.png') }}?v={{ filemtime($logoPath) }}"
             alt="{{ config('app.name', 'ISP Billing') }}"
             class="{{ $markClass }} object-contain rounded-lg bg-white">
    @else
        <div class="{{ $markClass }} rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold shadow-sm">
            ISP
        </div>
    @endif

    @if($showText)
        <div>
            <p class="{{ $textClass }} font-bold text-inherit">{{ config('app.name', 'ISP Billing') }}</p>
            <p class="text-xs opacity-70">Billing & Monitoring</p>
        </div>
    @endif
</div>
