{{-- <div>
    <a data-id="form{{$id}}" type="button"
        class="w-max btn btn-sm btn-danger no-wrap shadow mb-2 btn-delete d-flex align-items-center justify-content-center">
        <i style="font-size: 13px;" class="fas fa-trash me-1"></i>Delete
    </a>
    <form id="form{{$id}}" action="{{$action}}" method="post">
        @csrf
        @method('delete')
    </form>
</div> --}}

<div>
    <a data-id="form{{$id}}" type="button" id="btnDelete{{$id}}"
        class="btn-delete btn btn-icon btn-active-light-primary w-30px h-30px me-3">
        <i class="fas fa-trash-alt" data-id="form{{$id}}"></i>
    </a>
    <form id="form{{$id}}" action="{{$action}}" method="post">
        @csrf
        @method('delete')
    </form>
</div>