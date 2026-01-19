@php
$categoryItem = \App\Models\CategoryItem::find(@$value ?? 0);
@endphp
<select name="category_item_id" id="category_item_id" data-control="select2"
    class="form-select form-select-solid {{$class ?? ''}}" {{ $attributes }}>
    @if($categoryItem)
    <option selected value="{{@$categoryItem->id}}">{{$categoryItem->name}}</option>
    @endif
</select>

@push('js')
<script>
    $(document).ready(function(){
    $('#category_item_id').select2({
        placeholder: "Pilih Kategori Barang",
        ajax: {
            url: "{{route('select2')}}",
            dataType: 'json',
            delay: 300,
            data: function (params) {
                var queryParameters = {
                    search: params.term,
                    data_type : "CATEGORY_ITEM"
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