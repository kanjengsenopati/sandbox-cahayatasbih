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
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Data Wali Santri
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-end ms-4"></span>
                    <!--end::Separator-->
                </h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="<?php echo e(route('user.index')); ?>" class="text-muted text-hover-primary">Wali</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">Data Wali Santri</li>
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
            <div class="card mb-5">
                <!--begin::Card header-->
                <div
                    class="card-header d-flex flex-column flex-sm-row align-items-end justify-content-between border-0 pt-6">
                    <!-- Filter Section -->
                    <div class="d-flex flex-wrap gap-4 align-items-end mb-4 mb-sm-0">
                        <form action="#" id="form-filter" method="get">
                            <input type="text" hidden id="type" name="type" required>
                            <div class="d-flex flex-wrap gap-4 align-items-end">
                                <div>
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select form-select-sm" id="filter_status">
                                        <option value="">Semua</option>
                                        <option value="ACTIVE">Aktif</option> <!-- Opsi untuk Aktif -->
                                        <option value="INACTIVE">Tidak Aktif</option> <!-- Opsi untuk Tidak Aktif -->
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-4 align-items-end">
                        <?php if (isset($component)) { $__componentOriginal71c6471fa76ce19017edc287b6f4508c = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action.import','data' => ['target' => '#modalImport','name' => 'Wali Santri']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('action.import'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['target' => '#modalImport','name' => 'Wali Santri']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71c6471fa76ce19017edc287b6f4508c)): ?>
<?php $component = $__componentOriginal71c6471fa76ce19017edc287b6f4508c; ?>
<?php unset($__componentOriginal71c6471fa76ce19017edc287b6f4508c); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal71c6471fa76ce19017edc287b6f4508c = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.action.create','data' => ['name' => 'Wali Santri','action' => ''.e(route('user.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('action.create'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'Wali Santri','action' => ''.e(route('user.create')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71c6471fa76ce19017edc287b6f4508c)): ?>
<?php $component = $__componentOriginal71c6471fa76ce19017edc287b6f4508c; ?>
<?php unset($__componentOriginal71c6471fa76ce19017edc287b6f4508c); ?>
<?php endif; ?>
                    </div>

                    <!-- Stats Cards -->
                    <div class="d-flex flex-wrap gap-4 mt-4 w-100">
                        <!-- Card for "Wali Santri Aktif" -->
                        <div class="card bg-light-success flex-grow-1">
                            <div class="card-body d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-user-check text-success fs-2"></i> <!-- Ikon untuk aktif -->
                                </div>
                                <div>
                                    <div class="fw-bolder fs-5 text-gray-800">Wali Santri Aktif</div>
                                    <div class="text-success fs-3 fw-bolder" id="active-parents">0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Card for "Wali Santri Tidak Aktif" -->
                        <div class="card bg-light-danger flex-grow-1">
                            <div class="card-body d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-user-times text-danger fs-2"></i> <!-- Ikon untuk tidak aktif -->
                                </div>
                                <div>
                                    <div class="fw-bolder fs-5 text-gray-800">Wali Santri Tidak Aktif</div>
                                    <div class="text-danger fs-3 fw-bolder" id="inactive-parents">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table id="table-user" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="fw-bolder fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th width="3%">No</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Status</th>
                                    <th>Akses</th>
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
<!--end::Content-->
<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('user.import')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportLabel">Import Data Wali Santri</h5>
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
                        <a href="assets\media\template\import\Template Import Data Wali Santri.xlsx"
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
        var table = $('#table-user').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            searchable: true,
            ajax: {
                url: '<?php echo e(route('user.index')); ?>',
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.type = 'table';
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
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
                    data: 'name',
                    name: 'name',
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    render: function(data, type, row) {
                        return data ? data : 'N/A'; // Null handler
                    }
                },
                {
                    data: 'gender',
                    name: 'gender',
                    render: function(data, type, row) {
                        let badgeClass = '';
                        let label = '';
                        
                        if (data == 'L') {
                            badgeClass = 'badge-light-primary';
                            label = 'Laki-laki';
                        } else if (data == 'P') {
                            badgeClass = 'badge-light-danger';
                            label = 'Perempuan';
                        } else {
                            badgeClass = 'badge-light-warning';
                            label = 'Tidak diketahui';
                        }
                        
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    },
                },
                {
                    data: 'status',
                    name: 'status',
                },
                {
                    data: 'last_login',
                    name: 'last_login',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1,
                    render: function(data, type, row) {
                        return data ? data : 'No actions available'; // Null handler
                    }
                },
            ]
        });

        $('#filter_status').on('change', function() {
            table.ajax.reload();
        });

        $.ajax({
            url: '<?php echo e(route('user.index')); ?>',
            type: 'GET',
            data: {
                type: 'statistic'
            },
            success: function(response) {
                $('#active-parents').text(response.active);
                $('#inactive-parents').text(response.inactive);
            }
        });
    })
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.master', ['title' => 'Data User'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\Antigravity\Projects\cahayatasbih\resources\views/admins/user/index.blade.php ENDPATH**/ ?>