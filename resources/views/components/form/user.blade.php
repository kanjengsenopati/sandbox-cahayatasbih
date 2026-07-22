@php
$user = \App\Models\User::find(@$value ?? 0);
$statusText = '';
$phoneText = '';
if ($user) {
    $statusText = match($user->jamaah_status) {
        'JAMAAH' => 'Jamaah',
        'NON_JAMAAH' => 'Non Jamaah',
        'MUKIMIN' => 'Mukimin',
        default => 'Non Jamaah'
    };
    $phoneText = !empty($user->phone) ? ' - ' . $user->phone : '';
}
@endphp
<select name="user_id" id="user_id" data-control="select2" class="form-select form-select-solid {{$class ?? ''}}" {{
    $attributes }}>
    @if($user)
    <option selected value="{{@$user->id}}">{{$user->name}} [{{$statusText}}]{{$phoneText}}</option>
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
                        var phoneText = item.phone ? ' - ' + item.phone : '';
                        return {
                            text: item.name + ' [' + statusText + ']' + phoneText,
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