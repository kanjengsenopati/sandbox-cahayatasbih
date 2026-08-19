<script src="{{ url('https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
{{-- <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script> --}}
<script src="{{ asset('assets\js\vendors\plugins\sweetalert2.init.js') }}" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
<script src="{{ url('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js') }}"></script>
<script src="{{ url('https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@livewireScripts
@stack('js')
<script>
    // Set DataTables global defaults
    if (typeof $.fn.dataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                processing: `
                    <div class="text-center">
                        <div class="fs-4 fw-bolder text-dark mb-2">Mohon Tunggu</div>
                        <div class="fs-6 text-muted mb-4">Sedang memuat data...</div>
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `
            }
        });

        // Add custom CSS to force processing indicator to top with modern solid UI
        $("<style>")
            .prop("type", "text/css")
            .html("\
                div.dataTables_wrapper div.dataTables_processing {\
                    position: fixed !important;\
                    top: 50% !important;\
                    left: 50% !important;\
                    transform: translate(-50%, -50%) !important;\
                    margin-top: 0 !important;\
                    z-index: 10000 !important;\
                    background-color: #ffffff !important;\
                    box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.15) !important;\
                    border: none !important;\
                    border-radius: 12px !important;\
                    padding: 30px 40px !important;\
                    width: 320px !important;\
                    display: flex !important;\
                    flex-direction: column !important;\
                    align-items: center !important;\
                    justify-content: center !important;\
                }\
                .dt-processing-active {\
                    pointer-events: none;\
                }\
            ")
            .appendTo("head");

        // Listen to processing event globally to toggle active class
        $(document).on('processing.dt', function(e, settings, processing) {
            var wrapper = $(e.target).closest('.dataTables_wrapper');
            if (processing) {
                wrapper.addClass('dt-processing-active');
            } else {
                wrapper.removeClass('dt-processing-active');
            }
        });
    }

    // Translate input title to title_en and description to description_en when input title and description
    const translate = (input, output) => {
        if ($(input).val() != '') {
            axios.get('{{ route('translate') }}', {
                params: {
                    text: $(input).val(),
                }
            }).then(function(response) {
                $(output).val(response.data)
            })
        }
    }
    $(document).on('click', '.btn-delete', function(e) {
        var form = $("#" + $(this).data("id"));
        Swal.fire({
            title: 'Hapus Data',
            text: 'Anda yakin akan menghapus data ini ?, data yang telah dihapus tidak dapat dikembalikan',
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-success"
            }
        }).then((res) => {
            if (res.isConfirmed) {
                form.submit();
                Swal.fire({
                    title: 'loading...',
                    text: 'Mohon tunggu sebentar',
                    icon: 'info',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    },
                    timer: 2000,
                })
            } else {
                return false;
            }
        });
        return false;
    })
</script>
<script>
    $(document).on('click', '.btn-status', function(e) {
    e.preventDefault();

    // var form = $("#" + e.target.dataset.id);

    var form = $(e.target).closest('form');
    
    if (form.length === 0) {
    console.error('Form element not found.');
    return;
    }

    console.log("Button clicked");
    console.log("Form ID: " + e.target.dataset.id);
    console.log("Form Element: ", form);

    Swal.fire({
    title: 'Ubah Status Data',
    text: 'Apakah Anda yakin ingin mengubah status data ini?',
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: 'success',
    cancelButtonColor: 'primary',
    confirmButtonText: 'Ya',
    cancelButtonText: 'Batal',
    }).then((result) => {
    console.log("Swal Result: ", result);

    if (result.isConfirmed) {
    console.log("Submitting form...");
    form.submit();
    } else {
    console.log("Form submission cancelled.");
    }
    });
    });
</script>
@foreach (['success', 'error', 'warning', 'info'] as $message)
@if (session($message))
<script>
    Swal.fire({
                title: '{{ ucfirst($message) }}',
                text: {!! json_encode(session($message)) !!},
                icon: '{{ $message }}',
                confirmButtonText: 'Baik',
                customClass: {
                    confirmButton: "btn fw-bold btn-primary"
                }
            })
</script>
@endif
@endforeach
<script>
    $(document).on('keyup', '.input-money', function() {
        var val = $(this).val().replace(/\D/g, '');
        var n = parseInt(val, 10) || 0;
        if (n > 0) {
            $(this).val(n.toLocaleString('id-ID'));
        } else {
            $(this).val(0);
        }
    });

    $(document).on('submit', 'form', function() {
        $(this).find('.input-money').each(function() {
            this.value = this.value.replace(/[.,]/g, '');
        });
    });
</script>