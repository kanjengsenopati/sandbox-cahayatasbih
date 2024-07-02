{{-- <a href="{{ $action }}" class="btn btn-sm btn-primary shadow mb-2">
    <i style="font-size: 13px;" class="fas fa-paper-plane"></i>
    Send
</a> --}}
{{-- post to route broadcast.send --}}
<form action="{{ $action }}" method="POST" class="d-inline">
    @csrf
    <input type="hidden" name="id" value="{{ $id }}">
    <button type="submit" class="btn btn-sm btn-primary shadow mb-2">
        <i style="font-size: 13px;" class="fas fa-paper-plane"></i>
        Send
    </button>
</form>
