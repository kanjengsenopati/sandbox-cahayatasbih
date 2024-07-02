@if($status == 'APPROVED_BY_KADIV_HC')
<a href="{{ $action }}" class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin ingin membatalkan SPD ini?')"><i class="fa fa-times"></i> Batalkan SPD</a>
@endif