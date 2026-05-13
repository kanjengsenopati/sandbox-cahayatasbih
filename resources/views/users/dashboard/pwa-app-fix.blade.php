@extends('layouts.wali-pwa')

@section('content')
<div id="root"></div>

<!-- PRODUCTION ASSETS - HARD CODED, NO ENV CHECKS -->
@php
    // Try multiple possible locations for manifest (VPS vs Local)
    $possiblePaths = [
        public_path('portalwalisantri/dist/vite-manifest.json'),
        base_path('public/portalwalisantri/dist/vite-manifest.json'),
    ];
    
    $manifestPath = null;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $manifestPath = $path;
            break;
        }
    }

    $manifest = $manifestPath ? json_decode(file_get_contents($manifestPath), true) : [];
    $entry = $manifest['index.html'] ?? null;
@endphp

<!-- DEPLOYMENT VERSION v5: {{ date('Y-m-d H:i:s') }} | manifest={{ $manifestPath ? 'YES' : 'NO' }} | entry={{ $entry ? 'FOUND' : 'MISSING' }} -->

@if($entry)
    @foreach($entry['css'] ?? [] as $css)
        <link rel="stylesheet" href="/portalwalisantri/dist/{{ $css }}?v={{ time() }}">
    @endforeach
    <script type="module" src="/portalwalisantri/dist/{{ $entry['file'] }}?v={{ time() }}"></script>
@else
    <!-- FALLBACK: Direct asset loading if manifest logic fails -->
    <link rel="stylesheet" href="/portalwalisantri/dist/assets/styles-DepOk4a2.css?v={{ time() }}">
    <script type="module" src="/portalwalisantri/dist/assets/index-BXBPmMwx.js?v={{ time() }}"></script>
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
