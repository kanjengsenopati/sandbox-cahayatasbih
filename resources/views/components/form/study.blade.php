@php
$study = \App\Models\Study::find(@$value ?? 0);
@endphp
<select name="study_id" id="study_id" data-control="select2" class="form-select form-select-solid {{$class ?? ''}}" {{
    $attributes }}>
    @if($study)
    <option selected value="{{@$study->id}}">{{$study->name}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#study_id').select2({
        placeholder: "Pilih Mata Pelajaran",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "STUDY"
                }
                return queryParameters;
            },
            processResults: function (data) {
                return {
                results:  $.map(data, function (item) {
                        return {
                            text: item.name + ' - ' + item.kkm,
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