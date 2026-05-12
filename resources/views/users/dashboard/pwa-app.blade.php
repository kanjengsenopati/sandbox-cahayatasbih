@extends('layouts.wali-pwa')

@section('content')
<div id="root"></div>

<!-- SPA Entry Point -->
@if(app()->environment('local'))
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
    {{-- Build assets will be here after npm run build --}}
    @php
        $manifestPath = public_path('portalwalisantri/dist/.vite/manifest.json');
        $manifest = [];
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        }
    @endphp
    
    @if(isset($manifest['index.html']))
        @foreach($manifest['index.html']['css'] ?? [] as $css)
            <link rel="stylesheet" href="/portalwalisantri/dist/{{ $css }}">
        @endforeach
        <script type="module" src="/portalwalisantri/dist/{{ $manifest['index.html']['file'] }}"></script>
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
