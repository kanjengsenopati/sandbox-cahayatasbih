<?php $__env->startSection('content'); ?>
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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1"> Daftar Siswa</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="<?php echo e(route('student.index')); ?>" class="text-muted text-hover-primary">Siswa</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">List Siswa</li>
                    <!--end::Item-->

                </ul>
                <!--end::Breadcrumb-->
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div
                    class="card-header d-flex align-items-end gap-5 flex-sm-row mb-5 justify-content-between border-0 pt-6">
                    <div class="d-flex flex-wrap justify-content-between gap-5">
                        <div class="mb-3">
                            <label for="filter_school" class="form-label fw-bold">UPT</label>
                            <select class="form-select" id="filter_school">
                                <option value="">Semua UPT</option>
                                <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($school->id); ?>"><?php echo e($school->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_class" class="form-label fw-bold">Kelas</label>
                            <select class="form-select" id="filter_class">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="filter_status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="filter_status">
                                <option value="">Semua Status</option>
                                <option value="ACTIVE">Aktif</option>
                                <option value="INACTIVE">Tidak Aktif</option>
                                <option value="GRADUATED">Lulus</option>
                                <option value="DROPPED_OUT">Drop Out</option>
                                <option value="TRANSFERRED">Pindah</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-end">
                        
                            <div class="d-flex gap-2">
                                <a href="<?php echo e(route('student-barcode.index')); ?>" class="btn btn-primary btn-sm"><i
                                        class="fa fa-print me-2"></i>
                                    Barcode Santri</a>
                                <?php if (isset($component)) { $__componentOriginal71c6471fa76ce19017edc287b6f4508c = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action.import','data' => ['target' => '#modalImport','name' => 'Santri']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('action.import'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['target' => '#modalImport','name' => 'Santri']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71c6471fa76ce19017edc287b6f4508c)): ?>
<?php $component = $__componentOriginal71c6471fa76ce19017edc287b6f4508c; ?>
<?php unset($__componentOriginal71c6471fa76ce19017edc287b6f4508c); ?>
<?php endif; ?>
                                
                            <?php if (isset($component)) { $__componentOriginal71c6471fa76ce19017edc287b6f4508c = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action.create','data' => ['name' => 'Santri','action' => ''.e(route('student.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('action.create'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'Santri','action' => ''.e(route('student.create')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71c6471fa76ce19017edc287b6f4508c)): ?>
<?php $component = $__componentOriginal71c6471fa76ce19017edc287b6f4508c; ?>
<?php unset($__componentOriginal71c6471fa76ce19017edc287b6f4508c); ?>
<?php endif; ?>
                        </div>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-student" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th style="width: 3%">No</th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Wali Siswa</th>
                                    <th>UPT</th>
                                    <th>Saldo</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!--end::Table-->
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

<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('student.import')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">Import Data Santri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel</label>
                        <input class="form-control" type="file" name="file" id="file">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <a href="assets\media\template\import\Template Import Data Santri.xlsx"
                            class="btn btn-light-primary"><i class="fa fa-download"></i> Template</a>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('js'); ?>
<script>
    $(document).ready(() => {
        // Initialize DataTable
        var table = $('#table-student').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            searchDelay: 500,
            ajax: {
                url: '<?php echo e(route('student.index')); ?>',
                data: function(d) {
                    d.school_id = $('#filter_school').val();
                    d.classroom_id = $('#filter_class').val();
                    d.status = $('#filter_status').val();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'></i>",
                    "previous": "<i class='fa fa-angle-left'></i>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'nis',
                    name: 'nis',
                    orderable: true,
                    render: function(data, type, row) {
                        return data ? data : 'Belum diisi';
                    }
                },
                {
                    data: 'student',
                    name: 'student',
                    orderable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'parent',
                    name: 'parent',
                    orderable: false,
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'Belum ada';
                    },
                },
                {
                    data: 'school',
                    name: 'school',
                },
                {
                    data: 'saldo',
                    name: 'saldo',
                },
                {
                    data: 'status',
                    name: 'status',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });

        // Populate filter_class on school change
        $('#filter_school').on('change', function() {
            var school_id = $(this).val();
            var url = "<?php echo e(route('student.get-classroom', ':id')); ?>".replace(':id', school_id);

            $.get(url, function(data) {
                $('#filter_class').html('<option value="">Semua Kelas</option>');
                if (data.length > 0) {
                    $.each(data, function(index, value) {
                        $('#filter_class').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        });

        // Reload DataTable on filter change
        $('#filter_school, #filter_class, #filter_status').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.master', ['title' => 'Data Siswa'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/admins/student/index.blade.php ENDPATH**/ ?>