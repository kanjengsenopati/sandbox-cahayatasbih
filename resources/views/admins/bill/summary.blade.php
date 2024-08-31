@extends('layouts.master', ['title' => 'Bayar Tagihan'])

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!-- Toolbar -->
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!-- Page title -->
            <div class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Bayar Tagihan</h1>
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <li class="breadcrumb-item text-muted">
                        <a class="text-muted text-hover-primary">Data Pembayaran</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-dark">Bayar Tagihan</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Post -->
    <div class="post d-flex flex-column-fluid">
        <div id="kt_content_container" class="container-xxl">
            <div class="d-flex flex-column flex-lg-row">
                <!-- Content -->
                <div class="flex-lg-row-fluid me-lg-15 order-2 order-lg-1 mb-10 mb-lg-0">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h2 class="card-title mb-0">Ringkasan Tagihan</h2>

                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Informasi Siswa -->
                                <div class="col-md-6">
                                    <h3 class="h4 mb-3">Informasi Siswa</h3>
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Nama</th>
                                                <td>{{ $student->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Saldo</th>
                                                <td>Rp. {{ number_format($student->saldo ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Kelas</th>
                                                <td>{{ $student->classroom->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">NISN</th>
                                                <td>{{ $student->nisn ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Sekolah</th>
                                                <td>{{ $student->classroom?->school->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Wali Murid</th>
                                                <td>{{ $student->user?->name ?? '' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Informasi Tagihan -->
                                <div class="col-md-6">
                                    <h3 class="h4 mb-3">Informasi Tagihan</h3>
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Nama</th>
                                                <td>{{ $billType->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Tahun Ajaran</th>
                                                <td>{{ $billType->academicYear->name ?? '' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total Tagihan</th>
                                                <td>Rp. {{ number_format($billType['total_bill'] ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Total Dibayar</th>
                                                <td>Rp. {{ number_format($billType['total_paid'] ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Sisa Tagihan</th>
                                                <td>Rp. {{ number_format($billType['total_unpaid'] ?? 0) }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Status</th>
                                                <td>{{ $billType['total_unpaid'] == 0 ? 'LUNAS' : 'BELUM LUNAS' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <a href="{{ route('bill.index', ['student_id' => $student->id]) }}"
                                    class="btn btn-primary" style="background-color: #4D0C7A; border-color: #4D0C7A;">
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Bill Payment -->
                    <div class="card card-flush pt-3 mb-5 mb-lg-10">
                        <div class="card-header">
                            <div class="card-title">
                                <h2 class="fw-bolder">Pembayaran Tagihan</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 fw-bold gy-4"
                                    id="kt_subscription_products_table">
                                    <thead>
                                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%;">No</th>
                                            <th class="min-w-125px">Bulan</th>
                                            <th class="min-w-125px">Tagihan</th>
                                            <th class="min-w-125px">Metode Pembayaran</th>
                                            <th>Status</th>
                                            <th class="min-w-125px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600">
                                        @foreach (array_merge(range(7, 12), range(1, 6)) as $month)
                                        <tr>
                                            <form action="{{ route('bill.store') }}" method="post" class="form-bayar"
                                                enctype="multipart/form-data">
                                                @csrf
                                                <td>{{ $loop->iteration }}</td>
                                                <th class="min-w-125px">
                                                    {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                                </th>
                                                @php
                                                $billForMonth = $bills->where('month', $month)->first();
                                                $amount = $billForMonth ? number_format($billForMonth->amount, 0, ',',
                                                '.') : 0;
                                                @endphp
                                                <input type="hidden" name="bill_ids[]"
                                                    value="{{ $billForMonth->id ?? '' }}">
                                                <input type="hidden" name="pay_amount"
                                                    value="{{ $billForMonth->amount ?? '' }}">
                                                <input type="hidden" name="student_id" value="{{ $student->id ?? '' }}">
                                                <td>Rp {{ $amount ?? '' }}</td>
                                                <td>
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')
                                                    @if(optional($billForMonth->transactions->first())->paymentMethod?->type
                                                    == 'CASH')
                                                    <span class="badge badge-success">
                                                        @if ($billForMonth?->transactions?->first()?->admin)
                                                        Tunai Melalui {{
                                                        $billForMonth->transactions->first()->admin->name ?? '' }}
                                                        @endif
                                                        @if($billForMonth->transactions?->first()?->paid_at)
                                                        <br>Dibayar {{ $billForMonth->transactions->first()->paid_at }}
                                                        @endif
                                                    </span>
                                                    @elseif(optional($billForMonth->transactions->first())->paymentMethod?->type
                                                    == 'TRANSFER')
                                                    <span class="badge badge-success">
                                                        @if ($billForMonth?->transactions?->first()?->admin)
                                                        Transfer Diverifikasi oleh {{
                                                        $billForMonth->transactions->first()->admin->name ?? '' }}
                                                        @endif
                                                        @if($billForMonth->transactions?->first()?->paid_at)
                                                        <br>Dibayar {{ $billForMonth->transactions->first()->paid_at }}
                                                        @endif
                                                    </span>
                                                    @elseif(optional($billForMonth?->transactions?->first())->paymentMethod?->type
                                                    == "BALANCE")
                                                    <span class="badge badge-success">
                                                        @php
                                                        $transaction = $billForMonth?->transactions?->first();
                                                        $adminName = $transaction?->admin?->name ?? '';
                                                        $paidAt = $transaction?->paid_at;
                                                        @endphp

                                                        @if ($transaction?->admin_id)
                                                        Debit Saldo melalui {{ $adminName }}
                                                        @else
                                                        Debit Saldo
                                                        @endif

                                                        @if ($paidAt)
                                                        <br>Dibayar {{ $paidAt }}
                                                        @endif
                                                    </span>
                                                    @else
                                                    <span class="badge badge-success">
                                                        {{ $billForMonth->transactions->first()->paymentMethod->name ??
                                                        '' }}
                                                        @if($billForMonth->transactions?->first()?->paid_at)
                                                        <br>Dibayar {{ $billForMonth->transactions->first()->paid_at }}
                                                        @endif
                                                    </span>
                                                    @endif
                                                    @else
                                                    <select class="form-select payment-method-select"
                                                        name="payment_method" required>
                                                        <option value="">Metode Pembayaran</option>
                                                        @if ($student->saldo > $amount)
                                                        <option value="BALANCE">Saldo</option>
                                                        @endif
                                                        <option value="CASH">Tunai</option>
                                                    </select>
                                                    @endif
                                                </td>
                                                <td>{{ $billForMonth ? $billForMonth->translated_status : 'UNPAID'}}
                                                </td>
                                                <td>
                                                    @if (Auth::user()->can('Edit Status Tagihan'))
                                                    @if ($billForMonth)
                                                    <a onclick="changeStatus('{{ $billForMonth->id }}', '{{ $billForMonth->status == 'PAID' ? 'UNPAID' : 'PAID' }}')"
                                                        class="btn btn-success btn-sm">Ubah Status</a>
                                                    @else
                                                    <span>-</span>
                                                    <!-- Or any other fallback message -->
                                                    @endif
                                                    @endif
                                                    @if ($billForMonth && $billForMonth->status == 'PAID')
                                                    @elseif ($billForMonth && $billForMonth->status == 'UNPAID' &&
                                                    $billForMonth->transactions->first()?->payment_link)
                                                    <a href="{{ $billForMonth->transactions->first()->payment_link }}"
                                                        class="btn btn-primary btn-sm">Ke Halaman Pembayaran</a>
                                                    @elseif ($billForMonth)
                                                    <button onclick="disableButton(this)"
                                                        class="btn btn-primary btn-bayar btn-sm">Bayar</button>
                                                    @endif
                                                </td>
                                            </form>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function disableButton(el) {
        el.disabled = true;
        el.innerHTML = 'Loading...';
        el.form.submit();
    }

   function changeStatus(billId, status) {
        const form = document.createElement('form');
        form.action = "{{ route('bill.change-status') }}";
        form.method = 'post';
        form.enctype = 'multipart/form-data';

        form.innerHTML = `
        @csrf
        <input type="hidden" name="status" value="${status}">
        <input type="hidden" name="bill_id" value="${billId}">
        `;

        document.body.appendChild(form);
        form.submit();
    }
</script>
@endpush