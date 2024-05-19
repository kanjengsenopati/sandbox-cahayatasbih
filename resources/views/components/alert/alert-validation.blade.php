@if ($errors->any())
<div class="alert alert-danger alert-dismissible show fade">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ str_replace(['The','field is required'],['Form','harus diisi'],$error) }}</li>
        @endforeach
    </ul>
</div>
@endif