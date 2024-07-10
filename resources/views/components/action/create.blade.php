@if(isset($name) && Auth::user()->can('Create ' . $name))
<div class="">
    <a type="a" class="btn btn-sm btn-primary" id="btn_add_permission" href="{{ $action }}"><i class="fas fa-plus"></i>
        {{ $label ?? $name }}</a>
    <!--end::Primary button-->
</div>
@endif