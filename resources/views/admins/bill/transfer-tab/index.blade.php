<div class="card">
    <!--begin::Card header-->
    <div class="card-header d-flex align-items-center justify-content-between border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <h3 class="text-dark">Data Pembayaran Transfer</h3>
        </div>

        <!--end::Card title-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Table-->
        <div class="table-responsive">
            <table id="table-transfer" class="table align-middle table-row-dashed ">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Siswa</th>
                        <th>Jenis Pembayaran</th>
                        <th>Jumlah Pembayaran</th>
                        <th>Kode Unik</th>
                        <th>Bukti Transfer</th>
                        <th>Status</th>
                        <th class="text-center min-w-100px" style="width: 22%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-bold"></tbody>
            </table>
        </div>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
@push('js')
<script>
    function updateStatus(status, id) {
// Function to update the status if needed
console.log('Status updated to', status, 'for transaction', id);
// Show note textarea if status is "Ditolak"
const noteTextarea = document.getElementById(`note-${id}`);
if (status == 'REJECTED') {
if (noteTextarea.tagName.toLowerCase() === 'input') {
const textarea = document.createElement('textarea');
textarea.className = 'form-control mt-2';
textarea.name = 'note';
textarea.id = `note-${id}`;
textarea.placeholder = 'Note';
textarea.value = noteTextarea.value;
noteTextarea.replaceWith(textarea);
}
} else {
if (noteTextarea.tagName.toLowerCase() === 'textarea') {
const input = document.createElement('input');
input.type = 'hidden';
input.name = 'note';
input.id = `note-${id}`;
input.value = noteTextarea.value;
noteTextarea.replaceWith(input);
}
}
}

function saveStatus(id) {
const status = document.getElementById(`status-${id}`).value;
const note = document.getElementById(`note-${id}`).value || '';
console.log('Saving status', status, 'and note', note, 'for transaction', id);

// Tampilkan loader menggunakan SweetAlert
Swal.fire({
title: 'Menyimpan...',
text: 'Harap tunggu',
allowOutsideClick: false,
didOpen: () => {
Swal.showLoading();
}
});

// Simpan menggunakan axios ke route bill.update
axios.put(`{{ url('bill') }}/${id}`, {
status: status,
note: note,
_token: '{{ csrf_token() }}' // Pastikan Anda menyertakan CSRF token
})
.then((response) => {
    // Tampilkan pesan sukses menggunakan SweetAlert sesuai response dari server
    if (response.data.code == '200') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: response.data.message
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.data.message
        });
    }
    // reload data table
    $('#table-transfer').DataTable().ajax.reload();
})
.catch((error) => {
console.error('Error saving status', error);
// Tampilkan pesan error menggunakan SweetAlert
Swal.fire({
icon: 'error',
title: 'Gagal',
text: 'Terjadi kesalahan saat menyimpan data'
});
});
}
</script>
@endpush