@php
$billItem = \App\Models\BillItem::find(@$value ?? 0);
@endphp
<select name="bill_item_id" id="bill_item_id" data-control="select2"
    class="form-select form-select-solid {{$class ?? ''}}" {{ $attributes }}>
    @if($billItem)
    <option selected value="{{@$billItem->id}}">{{$billItem->name}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#bill_item_id').select2({
        placeholder: "Pilih Pos Bayar",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "BILL_ITEM"
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