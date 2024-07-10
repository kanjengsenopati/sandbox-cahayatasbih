@if(isset($name) && Auth::user()->can('Create ' . $name))
<div class="mb-0">
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="{{ $target }}">
        <i class="fas fa-file-import"></i> Import Data
    </button>
</div>
@endif