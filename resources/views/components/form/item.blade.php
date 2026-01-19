@php
$item = \App\Models\Item::find(@$value ?? 0);
@endphp
<select name="item_id" id="item_id" data-control="select2" class="form-select form-select-solid {{$class ?? ''}}" {{
    $attributes }}>
    @if($item)
    <option selected value="{{@$item->id}}">{{$item->name}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#item_id').select2({
        placeholder: "Pilih Barang",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "ITEM"
                }
                return queryParameters;
            },
            processResults: function (data) {
                return {
                results:  $.map(data, function (item) {
                        return {
                            text: item.code + " - " + item.name,
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