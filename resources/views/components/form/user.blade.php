@php
$user = \App\Models\User::find(@$value ?? 0);
$statusText = '';
if ($user) {
    $statusText = match($user->jamaah_status) {
        'JAMAAH' => 'Jamaah',
        'NON_JAMAAH' => 'Non Jamaah',
        'MUKIMIN' => 'Mukimin',
        default => 'Non Jamaah'
    };
}
@endphp
<select name="user_id" id="user_id" data-control="select2" class="form-select form-select-solid {{$class ?? ''}}" {{
    $attributes }}>
    @if($user)
    <option selected value="{{@$user->id}}">{{$user->name}} [{{$statusText}}]</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#user_id').select2({
        placeholder: "Pilih Wali Siswa",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "USER"
                }
                return queryParameters;
            },
            processResults: function (data) {
                return {
                results:  $.map(data, function (item) {
                        var statusText = 'Non Jamaah';
                        if (item.jamaah_status === 'JAMAAH') {
                            statusText = 'Jamaah';
                        } else if (item.jamaah_status === 'MUKIMIN') {
                            statusText = 'Mukimin';
                        }
                        return {
                            text: item.name + ' [' + statusText + ']',
                            id: item.id
                        }
                    })
                };
            },
            cache: true
        }
    });
});

</script>
@endpush