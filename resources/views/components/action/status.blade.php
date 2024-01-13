@if ($status == true)
{{-- <form action="{{ $action }}" method="POST" class="d-inline" id="status{{ $id }}">
    @csrf
    <input type="hidden" name="id" value="{{ $id }}">
    <a data-id="status{{ $id }}" type="button"
        class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 btn-status">
        <i class="fas fa-ban"></i>
    </a>
</form> --}}
@else
<form action="{{ $action }}" method="POST" class="d-inline" id="status{{ $id }}">
    @csrf
    <input type="hidden" name="id" value="{{ $id }}">
    <button type="button" data-id="status{{ $id }}"
        class="btn btn-icon btn-active-light-primary w-30px h-30px me-3 btn-status">
        <i class="fas fa-check"></i>
    </button>
</form>
@endif