@extends('layouts.wali-pwa')

@section('content')
<div id="root"></div>

<!-- PRODUCTION ASSETS - HARD CODED, NO ENV CHECKS -->
@php
    $manifestPath = public_path('portalwalisantri/dist/vite-manifest.json');
    $manifestExists = file_exists($manifestPath);
    $manifest = $manifestExists ? json_decode(file_get_contents($manifestPath), true) : [];
    $entryKey = 'node_modules/@tanstack/react-start/dist/plugin/default-entry/client.tsx';
    $entry = $manifest[$entryKey] ?? null;
@endphp

<!-- FORCE-DEBUG v4: {{ date('Y-m-d H:i:s') }} | manifest={{ $manifestExists ? 'YES' : 'NO' }} | path={{ $manifestPath }} | entry={{ $entry ? 'FOUND' : 'MISSING' }} -->

@if($entry)
    @foreach($entry['css'] ?? [] as $css)
        <link rel="stylesheet" href="/portalwalisantri/dist/{{ $css }}">
    @endforeach
    @foreach($entry['assets'] ?? [] as $asset)
        @if(str_ends_with($asset, '.css'))
            <link rel="stylesheet" href="/portalwalisantri/dist/{{ $asset }}">
        @endif
    @endforeach
    <script type="module" src="/portalwalisantri/dist/{{ $entry['file'] }}"></script>
@else
    <!-- FALLBACK: Direct asset loading without manifest -->
    <link rel="stylesheet" href="/portalwalisantri/dist/assets/styles-CUKemUB5.css">
    <script type="module" src="/portalwalisantri/dist/assets/index-CzI2t6V8.js?v={{ time() }}"></script>
@endif

<script>
    window.addEventListener('online', () => document.body.classList.remove('offline'));
    window.addEventListener('offline', () => document.body.classList.add('offline'));
</script>
<style>
    body.offline::before {
        content: "Anda sedang offline.";
        position: fixed; top: 0; left: 0; right: 0;
        background: #DC2626; color: white; text-align: center;
        padding: 8px; z-index: 9999; font-size: 12px; font-weight: bold;
    }
</style>
@endsection
