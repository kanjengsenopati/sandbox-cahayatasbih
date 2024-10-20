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
        placeholder: "Pilih Siswa",
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
                results: $.map(data, function (item) {
                    var nisText = item.nis ? item.nis + ' - ' : '';
                    // Format saldo menjadi rupiah dengan pemisah ribuan
                    var formattedSaldo = 'Rp ' + item.saldo.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    return {
                    text: nisText + item.name + ' - ' + formattedSaldo,
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