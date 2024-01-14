@php
$student = \App\Models\Student::find(@$value ?? 0);
@endphp
<select name="student_id" id="student_id" data-control="select2" class="form-select form-select-solid {{$class ?? ''}}"
    {{ $attributes }}>
    @if($student)
    <option selected value="{{@$student->id}}">{{$student->name}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#student_id').select2({
        placeholder: "Pilih Wali Siswa",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "STUDENT"
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