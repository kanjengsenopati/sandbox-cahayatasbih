@php
$bank = \App\Models\Bank::find(@$value ?? 0);
@endphp
<select name="bank_id" id="bank_id" data-control="select2" class="form-select form-select-solid {{$class ?? ''}}" {{
    $attributes }}>
    @if($bank)
    <option selected value="{{@$bank->id}}">{{$bank->name ?? ''}} - {{$bank->account_number ?? ''}} -
        {{$bank->account_name ?? ''}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#bank_id').select2({
        placeholder: "Pilih Bank",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "BANK",
                }
                return queryParameters;
            },
            processResults: function (data) {
                return {
                results:  $.map(data, function (item) {
                        return {
                            text: item.name + ' - ' + item.account_number + ' - ' + item.account_name,
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