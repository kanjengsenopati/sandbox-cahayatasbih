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
$elementId = $attributes->get('id', 'user_id');
@endphp
<select name="user_id" id="{{ $elementId }}" class="form-select form-select-solid {{$class ?? ''}}" {{
    $attributes->except('id') }}>
    @if($user)
    <option selected value="{{@$user->id}}">{{$user->name}} [{{$statusText}}]{{$phoneText}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    var $el = $('#{{ $elementId }}');
    if ($el.length && !$el.hasClass("select2-hidden-accessible")) {
        $el.select2({
            dropdownParent: $el.closest('.modal').length ? $el.closest('.modal') : $(document.body),
            placeholder: "Pilih Wali Siswa",
            allowClear: true,
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
    }
});
</script>
@endpush