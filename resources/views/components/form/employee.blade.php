@php
$employee = \App\Models\User::find(@$value ?? 0);
@endphp
<select name="user_id" id="user_id" class="s-select2 form-control {{$class ?? ''}}" {{ $attributes }}>
   @if($employee)
   <option selected value="{{@$employee->id}}">{{@$employee->nip}} - {{@$employee->name}} ({{@$employee->department->name}})</option>
   @endif
</select>

@push('js')
<script>
$(document).ready(function(){
    // var showAge = <?= @$showAge ?? false ?>;
    $('#user_id').select2({
        placeholder: "Pilih Karyawan",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "EMPLOYEE",
                }
                return queryParameters;
            },
            processResults: function (data) {
                return {
                results:  $.map(data, function (item) {
                        return {
                            text: item.nip +' - '+item.name.toUpperCase() +` (${item.department.name})`,
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
