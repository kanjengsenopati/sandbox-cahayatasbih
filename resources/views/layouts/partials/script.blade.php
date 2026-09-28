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
    // Set DataTables global defaults & unified modern loading indicator
    if (typeof $.fn.dataTable !== 'undefined') {
        $.fn.dataTable.ext.errMode = 'console';
        const UNIFIED_PROCESSING_HTML = `
            <div class="d-flex flex-column align-items-center justify-content-center">
                <div class="spinner-border text-primary mb-3" style="width: 2.2rem; height: 2.2rem; border-width: 0.22em;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="fs-6 fw-bolder text-gray-800 mb-1">Mohon Tunggu</div>
                <div class="fs-8 text-muted">Sedang memuat data...</div>
            </div>
        `;

        $.extend(true, $.fn.dataTable.defaults, {
            stateSave: true,
            stateDuration: 7200, // 2 hours
            language: {
                processing: UNIFIED_PROCESSING_HTML
            }
        });

        // Listen to processing event globally to toggle active class & guarantee consistent indicator UI
        $(document).on('processing.dt', function(e, settings, processing) {
            var wrapper = $(e.target).closest('.dataTables_wrapper');
            if (processing) {
                wrapper.addClass('dt-processing-active');
                var $proc = wrapper.find('div.dataTables_processing');
                if ($proc.length && (!$proc.find('.spinner-border').length || $proc.text().includes('Processing') || $proc.text().includes('Sedang memproses'))) {
                    $proc.html(UNIFIED_PROCESSING_HTML);
                }
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
        e.preventDefault();
        // Use currentTarget to always read data-id from the anchor/button, not from child icon
        var btn = $(e.currentTarget);
        var formId = btn.data('id') || btn.closest('[data-id]').data('id');
        var form = $('#' + formId);
        if (!form.length) return false;
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
                html: {!! json_encode(session($message)) !!},
                icon: '{{ $message }}',
                confirmButtonText: 'Baik',
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
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

<script type="speculationrules">
{
  "prefetch": [
    {
      "source": "document",
      "where": {
        "and": [
          { "href_matches": "/*" },
          { "not": { "href_matches": "*/logout" } },
          { "not": { "href_matches": "*/export*" } },
          { "not": { "href_matches": "*#*" } }
        ]
      },
      "eagerness": "moderate"
    }
  ]
}
</script>
<script>
    // 1. Instant Top Progress Bar on navigation click
    $(document).on('click', 'a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([download])', function(e) {
        if (e.which === 1 && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            var url = $(this).attr('href');
            if (url && (url.startsWith('/') || url.startsWith(window.location.origin))) {
                var $bar = $('#top-page-progress');
                if (!$bar.length) {
                    $bar = $('<div id="top-page-progress"></div>').appendTo('body');
                }
                $bar.css({ width: '0%', opacity: '1', display: 'block' });
                setTimeout(function() { $bar.css('width', '45%'); }, 10);
                setTimeout(function() { $bar.css('width', '85%'); }, 300);
            }
        }
    });

    window.addEventListener('pageshow', function() {
        var $bar = $('#top-page-progress');
        if ($bar.length) {
            $bar.css('width', '100%');
            setTimeout(function() {
                $bar.fadeOut(200, function() { $bar.css('width', '0%'); });
            }, 100);
        }
    });

    // 2. Fallback Hover Prefetching for browsers without Speculation Rules
    (function() {
        const prefetchedUrls = new Set();
        function prefetchUrl(url) {
            if (!url || prefetchedUrls.has(url)) return;
            if (url.startsWith('#') || url.startsWith('javascript:') || url.includes('/logout') || url.includes('export')) return;
            if (url.startsWith('/') || url.startsWith(window.location.origin)) {
                prefetchedUrls.add(url);
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                document.head.appendChild(link);
            }
        }

        $(document).on('mouseenter touchstart', '.menu-link[href], .nav-link[href], .breadcrumb-item a[href]', function() {
            var href = $(this).attr('href');
            prefetchUrl(href);
        });
    })();
</script>