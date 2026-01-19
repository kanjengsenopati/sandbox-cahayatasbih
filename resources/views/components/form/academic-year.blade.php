@php
$academicYear = \App\Models\AcademicYear::find(@$value ?? 0);
@endphp
<select name="academic_year_id" id="academic_year_id" data-control="select2"
    class="form-select form-select-solid {{$class ?? ''}}" {{ $attributes }}>
    @if($academicYear)
    <option selected value="{{@$academicYear->id}}">{{$academicYear->name}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#academic_year_id').select2({
        placeholder: "Pilih Tahun Ajaran",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "ACADEMIC_YEAR"
                }
                return queryParameters;
            },
            processResults: function (data) {
                return {
                results:  $.map(data, function (item) {
                        return {
                            text: item.name,
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