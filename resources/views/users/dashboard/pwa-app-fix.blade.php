@extends('layouts.wali-pwa')

@section('content')
<div id="root"></div>

<!-- PRODUCTION ASSETS - HARD CODED, NO ENV CHECKS -->
@php
    // Try multiple possible locations for manifest
    // Checking base_path directly bypasses any symlink issues with PHP file_exists
    $possiblePaths = [
        base_path('portalwalisantri/dist/client/vite-manifest.json'),
        base_path('portalwalisantri/dist/.vite/manifest.json'),
        base_path('portalwalisantri/dist/vite-manifest.json'),
        public_path('portalwalisantri/dist/client/vite-manifest.json'),
        base_path('public/portalwalisantri/dist/client/vite-manifest.json'),
    ];
    
    $manifestPath = null;
    $manifestUrlBase = '/portalwalisantri/dist/'; // Default fallback
    $manifest = [];
    $entry = null;
    $useBypass = false;
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $manifestPath = $path;
            
            $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
            $entry = $manifest['index.html'] ?? null;
            $fileRel = $entry['file'] ?? '';
            
            // Scenario 1: public/portalwalisantri points to portalwalisantri
            if ($fileRel && file_exists(public_path("portalwalisantri/dist/client/$fileRel"))) {
                $manifestUrlBase = '/portalwalisantri/dist/client/';
            } 
            // Scenario 2: public/portalwalisantri points to portalwalisantri/dist
            elseif ($fileRel && file_exists(public_path("portalwalisantri/client/$fileRel"))) {
                $manifestUrlBase = '/portalwalisantri/client/';
            }
            // Scenario 3: public/portalwalisantri points to portalwalisantri/dist/client
            elseif ($fileRel && file_exists(public_path("portalwalisantri/$fileRel"))) {
                $manifestUrlBase = '/portalwalisantri/';
            }
            // Fallbacks for older vite configs
            elseif ($fileRel && file_exists(public_path("portalwalisantri/dist/$fileRel"))) {
                $manifestUrlBase = '/portalwalisantri/dist/';
            }
            elseif ($fileRel && file_exists(public_path("portalwalisantri/assets/" . basename($fileRel)))) {
                $manifestUrlBase = '/portalwalisantri/';
            }
            else {
                // If it doesn't exist in public at all, we use the ULTIMATE BYPASS ROUTE
                $useBypass = true;
            }
            break;
        }
    }
@endphp

<!-- DEPLOYMENT VERSION v13: {{ date('Y-m-d H:i:s') }} | manifest={{ $manifestPath ? 'YES' : 'NO' }} | bypass={{ $useBypass ? 'YES' : 'NO' }} -->

@if($entry)
    @php
        $cssFile = $entry['css'][0] ?? null;
        $cssSysPath = $cssFile ? dirname($manifestPath) . '/' . $cssFile : null;
        $cssExists = $cssSysPath && file_exists($cssSysPath);
    @endphp
    <!-- DIAGNOSTICS: sysPath={{ $cssSysPath }} | exists={{ $cssExists ? 'YES' : 'NO' }} -->
    
    @if($useBypass)
        @foreach($entry['css'] ?? [] as $css)
            <link rel="stylesheet" href="/pwa-asset?f={{ urlencode($css) }}&v={{ time() }}">
        @endforeach
        <script type="module" src="/pwa-asset?f={{ urlencode($entry['file']) }}&v={{ time() }}"></script>
    @else
        @foreach($entry['css'] ?? [] as $css)
            <link rel="stylesheet" href="{{ $manifestUrlBase }}{{ $css }}?v={{ time() }}">
        @endforeach
        <script type="module" src="{{ $manifestUrlBase }}{{ $entry['file'] }}?v={{ time() }}"></script>
    @endif
@else
    <!-- FALLBACK: Direct asset loading if manifest logic fails (ensure this points to a built asset if needed) -->
    <script>console.error('PWA Manifest missing at ' + @json($possiblePaths));</script>
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
