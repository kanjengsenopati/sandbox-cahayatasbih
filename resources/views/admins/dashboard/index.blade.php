@extends('layouts.master', ['title' => $isOutletUser ? 'Dashboard Outlet' : 'Dashboard Utama'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack px-5">
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">
                    {{ $isOutletUser ? 'Dashboard Outlet' : 'Dashboard Utama' }}
                </h1>
            </div>
            @if($isOutletUser)
                @include('layouts.partials.outlet_switcher')
            @endif
        </div>
    </div>

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl px-5">
            
            @if($isOutletUser)
                @include('admins.dashboard.outlet')
            @else
                @include('admins.dashboard.academic')
            @endif

        </div>
    </div>
</div>
@endsection