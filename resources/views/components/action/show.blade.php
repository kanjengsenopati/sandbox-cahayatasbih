{{-- <div>
    <a class="btn btn-primary btn-sm shadow mb-2 d-flex align-items-center justify-content-center" href="{{$action}}">
        <i style="font-size: 13px;" class="{{ $icon ?? 'fa fa-info-circle me-1' }}"></i>
        {{$label ?? 'Detail'}}
    </a>
</div> --}}

<div>
    <a href="{{$action}}" class="btn btn-icon btn-active-light-primary w-30px h-30px me-3">
        <i class="fa fa-info-circle fs-3">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
        {{ $label ?? '' }}
    </a>
</div>