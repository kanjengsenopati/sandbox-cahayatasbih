@if(isset($name) && Auth::user()->can('Delete ' . $name))
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
@endif