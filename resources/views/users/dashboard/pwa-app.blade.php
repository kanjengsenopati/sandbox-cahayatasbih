@extends('layouts.wali-pwa')

@section('content')
<div id="root"></div>

<!-- SPA Entry Point -->
@php
    $manifestPath = public_path('portalwalisantri/dist/.vite/manifest.json');
    $manifestExists = file_exists($manifestPath);
    $useProduction = $manifestExists && !app()->environment('local');
    
    // Force production if manifest exists but environment is still local (common on VPS)
    if ($manifestExists) {
        $useProduction = true;
    }
    
    $manifest = [];
    if ($manifestExists) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
    }
@endphp

<!-- Debug Info:
    Manifest Path: {{ $manifestPath }}
    Manifest Exists: {{ $manifestExists ? 'Yes' : 'No' }}
    Use Production: {{ $useProduction ? 'Yes' : 'No' }}
    App Env: {{ app()->environment() }}
-->

@if(!$useProduction && app()->environment('local'))
    <script type="module">
        import RefreshRuntime from 'http://localhost:5173/@react-refresh'
        RefreshRuntime.injectIntoGlobalHook(window)
        window.$RefreshReg$ = () => {}
        window.$RefreshSig$ = () => (type) => type
        window.__vite_plugin_react_preamble_installed__ = true
    </script>
    <script type="module" src="http://localhost:5173/@@vite/client"></script>
    <script type="module" src="http://localhost:5173/src/main.tsx"></script>
@else
    @php
        $entryKey = 'node_modules/@tanstack/react-start/dist/plugin/default-entry/client.tsx';
        $entry = $manifest[$entryKey] ?? null;
    @endphp
    
    @if($entry)
        @foreach($entry['assets'] ?? [] as $asset)
            @if(str_ends_with($asset, '.css'))
                <link rel="stylesheet" href="/portalwalisantri/dist/{{ $asset }}">
            @endif
        @endforeach
        
        {{-- Also check for css directly in entry if assets doesn't cover it --}}
        @foreach($entry['css'] ?? [] as $css)
            <link rel="stylesheet" href="/portalwalisantri/dist/{{ $css }}">
        @endforeach

        <script type="module" src="/portalwalisantri/dist/{{ $entry['file'] }}"></script>
    @else
        {{-- Fallback if manifest fails but files exist --}}
        <link rel="stylesheet" href="/portalwalisantri/dist/assets/styles-DwM8hKnt.css">
        <script type="module" src="/portalwalisantri/dist/assets/index-Bu1hofVw.js"></script>
    @endif
@endif

<script>
    // Handle offline status
    window.addEventListener('online', () => {
        document.body.classList.remove('offline');
    });
    window.addEventListener('offline', () => {
        document.body.classList.add('offline');
    });
</script>

<style>
    body.offline::before {
        content: "Anda sedang offline. Beberapa fitur mungkin tidak tersedia.";
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: #DC2626;
        color: white;
        text-align: center;
        padding: 10px;
        z-index: 9999;
        font-size: 12px;
        font-weight: bold;
    }
</style>
@endsection
