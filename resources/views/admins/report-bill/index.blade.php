@extends('layouts.master', ['title' => 'Laporan Tagihan'])
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Laporan</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('report-bill.index') }}" class="text-muted text-hover-primary">Laporan
                            Tagihan</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Tagihan</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card mb-5">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h3 class="text-dark">Laporan Tagihan Pembayaran</h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body">
                    <form id="filter_form" method="GET">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_school_id" class="form-label">UPT</label>
                                <select name="school_id" class="form-select" id="filter_school_id">
                                    <option value="">Pilih Pendidikan</option>
                                    @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_classroom_id" class="form-label">Kelas</label>
                                <select name="classroom_id" class="form-select" id="filter_classroom_id">
                                    <option value="">Pilih Kelas</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_academic_year_id" class="form-label">Tahun Ajaran</label>
                                <select name="academic_year_id" class="form-select" id="filter_academic_year_id">
                                    <option value="">Pilih Tahun Ajaran</option>
                                    @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_bill_type_id" class="form-label">Tagihan</label>
                                <select name="bill_type_id" class="form-select select2" id="filter_bill_type_id">
                                    <option value="">Semua Tagihan</option>
                                    @foreach ($billTypes as $billType)
                                    <option value="{{ $billType->id }}">{{ $billType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select class="form-select" id="filter_status" name="status">
                                    <option value="">Pilih Status</option>
                                    <option value="PAID">Lunas</option>
                                    <option value="UNPAID">Belum Lunas</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 align-self-end">
                                <button onclick="searchData()" class="btn btn-primary">Tampilkan</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
                    <!--begin::Card title-->
                    <div class="card-title">
                        {{-- <h3 class="text-dark">Sekolah</h3> --}}
                    </div>
                    <div class="">
                        {{-- <a type="a" class="btn btn-sm btn-primary" id="btn_add_permission"
                            href="{{ route('report-bill.create') }}">+ Sekolah</a> --}}
                        <!--end::Primary button-->
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-report-bill" class="table align-middle table-row-dashed ">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-bold"></tbody>
                        </table>
                    </div>
                    <!--end::Table-->

                    <!-- Add Button for WA Blast -->
                    <div class="text-center mt-3">
                        <button class="btn btn-success" id="send-blast-notif"><i class="bi bi-whatsapp"></i>
                            <span class="indicator-label" id="buttonText">Kirim
                                Notif
                                Tagihan WA</span>
                            <span class="indicator-progress d-none">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span></button>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
            <!--begin::Modals-->

        </div>
        <!--end::Container-->
    </div>
    <!--end::Post-->
</div>
@endsection
@push('js')
<script>
    // onchange school_id get data classrom on school
        $('#filter_school_id').on('change', function() {
            var school_id = $(this).val();
            $.ajax({
                url: "{{ route('report-bill.get-classroom') }}",
                type: "GET",
                data: {
                    school_id: school_id
                },
                success: function(response) {
                    console.log(response);
                    $('#filter_classroom_id').empty();
                    if (response.data.length > 0) {
                        $('#filter_classroom_id').append('<option value="">Semua Kelas</option>');
                        $.each(response.data, function(key, value) {
                            $('#filter_classroom_id').append('<option value="' + value.id + '">' + value.name +
                                '</option>');
                        });
                    } else {
                        $('#filter_classroom_id').append('<option value="">Tidak ada kelas</option>');
                    }
                }
            });
        });

       $(document).ready(function() {
    // Inisialisasi DataTables
    var table = $('#table-report-bill').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
    url: "{{ route('report-bill.get-data') }}",
    data: function(d) {
    // Mengambil data filter dari elemen formulir
    d.school_id = $('#filter_school_id').val();
    d.classroom_id = $('#filter_classroom_id').val();
    d.academic_year_id = $('#filter_academic_year_id').val();
    d.bill_type_id = $('#filter_bill_type_id').val();
    d.status = $('#filter_status').val();
    }
    },
    columns: [
    {
    data: null,
    sortable: false,
    searchable: false,
    render: function(data, type, row, meta) {
    return meta.row + meta.settings._iDisplayStart + 1;
    }
    },
    { data: 'nis', name: 'nis' },
    { data: 'nisn', name: 'nisn' },
    { data: 'name', name: 'name' },
    { data: 'classroom.name', name: 'classroom.name' },
    {
    data: 'action',
    name: 'action',
    orderable: false,
    searchable: false
    }
    ]
    });
    
    // Fungsi untuk memperbarui data tabel saat melakukan pencarian
    function searchData() {
        table.ajax.reload();
    }
    
    // Event saat tombol "Tampilkan" ditekan
    $('#btn_tampilkan').click(function() {
    searchData();
    });
    
    // Event saat formulir filter disubmit
    $('#filter_form').submit(function(event) {
    event.preventDefault(); // Mencegah aksi default saat submit
    searchData();
    });

 // Send Bill Notif on click send-blast-notif
$('#send-blast-notif').on('click', function() {
var button = $(this);
changeButtonText(button, true); // Change button text and disable button
// get data filter
var school_id = $('#filter_school_id').val();
var classroom_id = $('#filter_classroom_id').val();
var academic_year_id = $('#filter_academic_year_id').val();
var bill_type_id = $('#filter_bill_type_id').val();
var status = $('#filter_status').val();

// send data to form use ajax
$.ajax({
url: "{{ route('report-bill.send-bill-notification') }}",
type: "POST",
data: {
_token: "{{ csrf_token() }}", // Add CSRF token here
school_id: school_id,
classroom_id: classroom_id,
academic_year_id: academic_year_id,
bill_type_id: bill_type_id,
status: status
},
success: function(response) {
if (response.code == 200) {
toastr.success("Berhasil mengirim notifikasi tagihan ke WhatsApp");
} else {
toastr.error(response.message);
}
},
error: function(xhr, status, error) {
toastr.error("Terjadi kesalahan saat mengirim notifikasi: " + error);
},
complete: function() {
changeButtonText(button, false); // Restore button text and enable button
}
});
});

function changeButtonText(button, sending) {
var buttonText = button.find('.indicator-label');
var indicatorProgress = button.find('.indicator-progress');

if (sending) {
buttonText.text('Mengirim Notifikasi...');
button.attr('disabled', true);
indicatorProgress.removeClass('d-none');
} else {
buttonText.text('Kirim Notif Tagihan WA');
button.removeAttr('disabled');
indicatorProgress.addClass('d-none');
}
}
});

</script>
@endpush