@php
$semester = \App\Models\Semester::find(@$value ?? 0);
@endphp
<select name="semester_id" id="semester_id" data-control="select2"
    class="form-select form-select-solid {{$class ?? ''}}" {{ $attributes }}>
    @if($semester)
    <option selected value="{{@$semester->id}}">{{$semester->name}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function() {
        $('#semester_id').select2({
            placeholder: "Pilih Semester",
            ajax: {
                url: "{{route('select2')}}",
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    var queryParameters = {
                        search: params.term,
                        data_type: "SEMESTER"
                    }
                    return queryParameters;
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
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