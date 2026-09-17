<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\Student;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\PointOfSaleCart;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PointOfSaleTransaction;
use App\Models\PointOfSaleTransactionDetail;
use App\Models\StockHistory;
use Illuminate\Support\Facades\Cache;

class OrderItemController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if ($user) {
                $mode = $request->input('mode') ?? request('mode');
                if (($user->isKasirKoperasi() || $user->isKoordinatorCahayaMart()) && $mode === 'outlet') {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses untuk modul Outlet.'], 403);
                    }
                    return redirect()->route('order-item.index', ['mode' => 'kantin'])->with('error', 'Maaf, Anda tidak memiliki akses untuk modul Outlet');
                }
                if ($user->isKasirOutlet() && $mode === 'kantin') {
                    if ($request->ajax()) {
                        return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses untuk modul Kantin/Koperasi.'], 403);
                    }
                    return redirect()->route('order-item.index', ['mode' => 'outlet'])->with('error', 'Maaf, Anda tidak memiliki akses untuk modul Kantin/Koperasi');
                }
            }
            return $next($request);
        });
    }


    public function dashboard()
    {
        $admin = auth()->user();
        $authOutletIds = $admin->getOutletIds();
        $totalTransaction = PointOfSaleTransaction::where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->when(!empty($authOutletIds), function($q) use ($authOutletIds) {
                $q->whereIn('outlet_id', $authOutletIds);
            })->count();
            
        $totalProfit = PointOfSaleTransactionDetail::whereHas('pointOfSaleTransaction', function ($query) use ($authOutletIds) {
            $query->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                  ->when(!empty($authOutletIds), function($q) use ($authOutletIds) {
                      $q->whereIn('outlet_id', $authOutletIds);
                  });
        })->sum('total');
        
        $totalSellingProduct = PointOfSaleTransactionDetail::whereHas('pointOfSaleTransaction', function ($query) use ($authOutletIds) {
            $query->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                  ->when(!empty($authOutletIds), function($q) use ($authOutletIds) {
                      $q->whereIn('outlet_id', $authOutletIds);
                  });
        })->sum('quantity');
        
        $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        $totalItemAvailable = Item::where('stock', '>', 0)->where('is_active', true)
            ->when(!empty($authOutletIds), function($q) use ($authOutletIds, $koperasiId) {
                $q->where(function($query) use ($authOutletIds, $koperasiId) {
                    $query->whereIn('outlet_id', $authOutletIds);
                    if (in_array($koperasiId, $authOutletIds)) {
                        $query->orWhereNull('outlet_id');
                    }
                });
            })->count();
            
        $totalItem = Item::where('is_active', true)
            ->when(!empty($authOutletIds), function($q) use ($authOutletIds, $koperasiId) {
                $q->where(function($query) use ($authOutletIds, $koperasiId) {
                    $query->whereIn('outlet_id', $authOutletIds);
                    if (in_array($koperasiId, $authOutletIds)) {
                        $query->orWhereNull('outlet_id');
                    }
                });
            })->count();
            
        $totalStudent = Student::count();

        $statistic = [
            'totalTransaction' => $totalTransaction,
            'totalProfit' => $totalProfit,
            'totalSellingProduct' => $totalSellingProduct,
            'totalItemAvailable' => $totalItemAvailable,
            'totalItem' => $totalItem,
            'totalStudent' => $totalStudent,
        ];

        if (request()->ajax()) {
            return $this->getItemsDataTable();
        }

        return view('admins.pos.dashboard', compact('statistic'));
    }

    private function getItemsDataTable()
    {
        $admin = auth()->user();
        $authOutletIds = $admin->getOutletIds();

        $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        $data = Item::where('is_active', true)->with('categoryItem')
            ->when(!empty($authOutletIds), function($q) use ($authOutletIds, $koperasiId) {
                $q->where(function($query) use ($authOutletIds, $koperasiId) {
                    $query->whereIn('outlet_id', $authOutletIds);
                    if (in_array($koperasiId, $authOutletIds)) {
                        $query->orWhereNull('outlet_id');
                    }
                });
            })
            ->get()->sortByDesc('total_selling');

        return DataTables::of($data)
            ->addColumn('status', function ($data) {
                return $data->is_active == 1 ? '<span class="badge badge-success">Aktif</span>' :
                    '<span class="badge badge-danger">Tidak Aktif</span>';
            })
            ->addColumn('category', function ($data) {
                return $data->categoryItem->name;
            })
            ->addColumn('total_selling', function ($data) {
                return $data->total_selling;
            })
            ->editColumn('price', function ($data) {
                return 'Rp. ' . number_format($data->selling_price, 0, ',', '.');
            })
            ->addColumn('action', function ($data) {
                $actionEdit = route('item.edit', $data->id);
                $actionDelete = route('item.destroy', $data->id);
                return "<div class='d-flex justify-content-center'>" .
                    view('components.action.edit', ['action' => $actionEdit]) .
                    view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                    "</div>";
            })
            ->rawColumns(['action', 'status', 'total_selling'])
            ->make(true);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (!Auth::user()->can('Manage Pos Kasir')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.order-item.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Authorization
        if (!Auth::user()->can('Create Pos Kasir')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses.');
        }

        // 2. Validation
        $request->validate([
            'payment_method' => 'required',
            'barcode' => 'required_if:payment_method,Saldo|nullable',
        ]);

        $adminId = auth()->id();

        // 3. IDEMPOTENCY CHECK (Atomic Lock)
        // Untuk pembayaran SALDO: kunci berdasarkan BARCODE SANTRI (bukan admin),
        // karena yang perlu dilindungi adalah data saldo santri dari concurrent kasir.
        // Untuk pembayaran TUNAI: kunci berdasarkan admin ID (mencegah double-submit kasir).
        if ($request->payment_method === PointOfSaleTransaction::PAYMENT_SALDO && $request->barcode) {
            $lockKey = 'pos_student_saldo_lock_' . $request->barcode;
        } else {
            $lockKey = 'pos_submit_lock_' . $adminId;
        }
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Transaksi sedang diproses, mohon tunggu sebentar.');
        }

        DB::beginTransaction();

        try {
            if ($request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO && !$request->barcode) {
                throw new \Exception('Pembayaran Saldo harus scan barcode siswa');
            }

            // 4. OPTIMASI N+1: Gunakan Eager Loading 'item'
            $carts = PointOfSaleCart::with('item') // Load relasi item di sini
                ->where('admin_id', $adminId)
                ->get();

            if ($carts->isEmpty()) {
                throw new \Exception('Keranjang masih kosong');
            }

            // Calculate totals (Memory efficient)
            $total = $carts->sum('total');
            
            // N+1 Fixed: Karena 'item' sudah di-load, akses ini tidak query lagi ke DB
            $totalProfit = $carts->sum(fn($cart) => $cart->item->profit * $cart->quantity);

            $admin = auth()->user();
            $outletId = $admin->getEffectiveOutletId(request('mode'), request('outlet_id'));

            $student = null;
            $historyId = null;

            // Process Payment
            if ($request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO) {
                // 5. DATA CONSISTENCY: Gunakan lockForUpdate()
                // Ini mencegah saldo dipotong ganda jika ada race condition database
                $student = Student::where('barcode', $request->barcode)->lockForUpdate()->first();

                // Pindahkan validasi ke dalam try-catch agar pesan error tertangkap rapi
                if (!$this->validateStudentForTransaction($student, $total)) {
                    // Pesan error sudah di-flash di dalam function validate
                    throw new \Exception(session('error') ?? 'Validasi siswa gagal.');
                }

                $balanceBefore = $student->saldo;
                // Gunakan decrement atomic di DB level dengan WHERE guard non-negatif
                $affected = Student::where('id', $student->id)
                    ->where('saldo', '>=', $total)
                    ->decrement('saldo', $total);

                if ($affected === 0) {
                    throw new \Exception('Maaf, saldo santri tidak mencukupi untuk transaksi ini.');
                }

                // Re-read nilai saldo terbaru dari DB untuk balance_after yang akurat
                $student->refresh();
                $balanceAfter = $student->saldo;

                $history = $this->recordSaldoHistory($student, $total, $balanceBefore, $balanceAfter, $outletId);
                $historyId = $history->id;
            }

            $outletModel = \App\Models\Outlet::find($outletId);
            $outletCode = $outletModel ? $outletModel->code : 'CHM';

            // Generate Transaction Code (Collision-free timestamp + random string)
            $paymentCode = 'POS-' . $outletCode . '-' . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));

            // Save Transaction
            $transaction = PointOfSaleTransaction::create([
                'student_id' => $student ? $student->id : null,
                'admin_id' => $adminId,
                'outlet_id' => $outletId,
                'payment_code' => $paymentCode,
                'pay_amount' => $total,
                'paid_at' => now(),
                'status' => PointOfSaleTransaction::STATUS_SUCCESS,
                'saldo_history_id' => $historyId,
                'profit' => $totalProfit,
                'type' => $request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO 
                            ? PointOfSaleTransaction::TYPE_SANTRI 
                            : PointOfSaleTransaction::TYPE_UMUM,
            ]);

            // Save Details
            $transactionDetails = $carts->map(function ($cart) {
                return [
                    'item_id' => $cart->item_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                    'total' => $cart->total,
                ];
            })->toArray();

            $transaction->pointOfSaleTransactionDetails()->createMany($transactionDetails);

            // 6. KHUSUS MODE OUTLET: Pengurangan Stok Atomik & Log StockHistory (OUT)
            $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

            if ($outletId !== $koperasiId) {
                foreach ($carts as $cart) {
                    $item = Item::where('id', $cart->item_id)->lockForUpdate()->first();
                    if ($item) {
                        if ($item->stock < $cart->quantity) {
                            throw new \Exception("Stok barang {$item->name} tidak mencukupi (sisa: {$item->stock})");
                        }
                        $item->decrement('stock', $cart->quantity);

                        StockHistory::create([
                            'item_id' => $cart->item_id,
                            'outlet_id' => $outletId,
                            'admin_id' => $adminId,
                            'quantity' => $cart->quantity,
                            'type' => StockHistory::TYPE_OUT,
                            'notes' => 'Penjualan POS Outlet ' . $paymentCode,
                        ]);
                    }
                }
            }

            // 7. OPTIMASI DELETE: Hapus bulk via Query Builder (1 Query)
            PointOfSaleCart::where('admin_id', $adminId)->delete();

            DB::commit();

            $message = 'Yeay! Transaksi berhasil';
            if ($student) {
                $message .= ', Saldo ' . $student->name . ' dikurangi Rp. ' . number_format($total, 0, ',', '.');
            }

            return redirect()->route('order-item.index')->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        } finally {
            optional($lock)->release();
        }
    }

    private function validateStudentForTransaction($student, $total): bool
    {
        if (!$student) {
            session()->flash('error', 'Santri tidak ditemukan.');
            return false;
        }

        if ($student->saldo < $total) {
            session()->flash('error', 'Maaf, Saldo Santri tidak mencukupi.');
            return false;
        }

        if ($student->is_blocked) {
            session()->flash('error', 'Maaf, Saldo Santri diblokir oleh Wali Santri.');
            return false;
        }

        $effectiveLimit = $student->getEffectiveDailyLimit();

        if ($effectiveLimit > 0) {
            // Optimasi: Cek transaksi harian
            // Karena kita sudah pakai lockForUpdate di $student, 
            // kalkulasi ini relatif aman selama transaksi lain juga me-lock row student yang sama.
            $totalThisDay = PointOfSaleTransaction::where('student_id', $student->id)
                ->whereDate('paid_at', now())
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->sum('pay_amount');

            if ($effectiveLimit < ($totalThisDay + $total)) {
                session()->flash('error', 'Maaf, Siswa telah mencapai batas transaksi harian.');
                return false;
            }
        }

        return true;
    }

    private function recordSaldoHistory($student, $total, $balanceBefore, $balanceAfter, $outletId = null)
    {
        $history = SaldoHistory::create([
            'student_id' => $student->id,
            'outlet_id' => $outletId,
            'type' => 'OUT',
            'amount' => $total,
            'description' => 'Pembayaran Pembelian Barang Rp. ' . number_format($total, 0, ',', '.'),
            'status' => 'SUCCESS',
            'usage' => SaldoHistory::USAGE_POS,
            'balance_before' => $balanceBefore ?? 0,
            'balance_after' => $balanceAfter ?? 0,
        ]);

        \App\Services\SaldoRecalculatorService::recalculateForStudent($student->id);

        return $history;
    }

    // public function store(Request $request)
    // {
    //     if (!Auth::user()->can('Create Pos Kasir')) {
    //         return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
    //     }
    //     $request->validate([
    //         'payment_method' => 'required',
    //         // 'student_id' => 'required_if:payment_method,Saldo|nullable|exists:students,id',
    //         'barcode' => 'required_if:payment_method,Saldo|nullable',
    //     ]);

    //     // Use database transaction to ensure data consistency
    //     DB::beginTransaction();

    //     try {

    //         if ($request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO && !$request->barcode) {
    //             return redirect()->back()->with('error', 'Maaf, Pembayaran Saldo harus scan barcode siswa');
    //         }

    //         $adminId = auth()->user()->id;

    //         // Get cart data
    //         $carts = PointOfSaleCart::where('admin_id', $adminId)->get();
    //         if ($carts->isEmpty()) {
    //             return redirect()->back()->with('error', 'Keranjang masih kosong');
    //         }

    //         // Calculate total omzet
    //         $total = $carts->sum('total');

    //         // calculate total profit on all cart from $cart->item->profit
    //         $totalProfit = $carts->sum(function ($cart) {
    //             return $cart->item->profit * $cart->quantity;
    //         });

    //         // Get student data and process payment
    //         if ($request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO) {
    //             // Fetch the student by barcode
    //             $student = Student::where('barcode', $request->barcode)->first();

    //             // Validate student's balance, block status, and daily limit
    //             if (!$this->validateStudentForTransaction($student, $total)) {
    //                 return redirect()->back()->with('error', 'Maaf, transaksi tidak dapat diproses.');
    //             }

    //             // Calculate balances before and after the transaction
    //             $balanceBefore = $student->saldo;
    //             $student->saldo -= $total;
    //             $balanceAfter = $student->saldo;

    //             // Save the updated student balance
    //             $student->save();

    //             // Record the transaction in SaldoHistory
    //             $history = $this->recordSaldoHistory($student, $total, $balanceBefore, $balanceAfter);
    //         }

    //         $paymentCode = 'POS-' . now()->format('Ymd') . str_pad(PointOfSaleTransaction::whereDate('paid_at', now())->count() + 1, 3, '0', STR_PAD_LEFT);
    //         // Save transaction data
    //         $transaction = PointOfSaleTransaction::create([
    //             'student_id' => $request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO ? $student->id : null,
    //             'admin_id' => $adminId,
    //             'payment_code' => $paymentCode,
    //             'pay_amount' => $total,
    //             'paid_at' => now(),
    //             'status' => PointOfSaleTransaction::STATUS_SUCCESS,
    //             'saldo_history_id' => $request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO ? $history->id : null,
    //             'profit' => $totalProfit,
    //             'type' => $request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO ? PointOfSaleTransaction::TYPE_SANTRI : PointOfSaleTransaction::TYPE_UMUM,
    //         ]);

    //         // Add transaction details
    //         $transactionDetails = $carts->map(function ($cart) {
    //             return [
    //                 'item_id' => $cart->item_id,
    //                 'quantity' => $cart->quantity,
    //                 'price' => $cart->price,
    //                 'total' => $cart->total,
    //             ];
    //         })->toArray();

    //         $transaction->pointOfSaleTransactionDetails()->createMany($transactionDetails);

    //         // Delete cart
    //         $carts->each->delete();

    //         // Commit the transaction
    //         DB::commit();

    //         if ($request->payment_method == PointOfSaleTransaction::PAYMENT_SALDO) {
    //             return redirect()->route('order-item.index')->with('success', 'Yeay! Transaksi berhasil, Saldo ' . $student->name . ' dikurangi Rp. ' . number_format($total, 0, ',', '.'));
    //         } else {
    //             return redirect()->route('order-item.index')->with('success', 'Yeay! Transaksi berhasil');
    //         }
    //     } catch (\Exception $e) {
    //         // If an exception occurs, rollback the transaction
    //         DB::rollback();
    //         return redirect()->back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
    //     }
    // }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getStudentByBarcode(Request $request)
    {
        $barcode = $request->barcode;
        $student = Student::with('classroom')
            ->where(function ($query) use ($barcode) {
                $query->where('barcode', $barcode)
                      ->orWhere('nis', $barcode)
                      ->orWhere('nisn', $barcode);
            })
            ->first();
            
        if (!$student) {
            return $this->postSuccessResponse("Data siswa tidak ditemukan", null);
        }

        $effectiveLimit = $student->getEffectiveDailyLimit();
        $student->effective_daily_limit = $effectiveLimit;
        
        if ($effectiveLimit > 0) {
            $totalThisDay = PointOfSaleTransaction::where('student_id', $student->id)
                ->whereDate('paid_at', now())
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->sum('pay_amount');
                
            $student->total_this_day = $totalThisDay;
            $student->remaining_limit = max(0, $effectiveLimit - $totalThisDay);
        } else {
            $student->total_this_day = 0;
            $student->remaining_limit = 0;
        }

        return $this->postSuccessResponse("Data siswa ditemukan", $student);
    }

    public function getCartData()
    {
        $admin = auth()->user();
        $outletId = $admin->getEffectiveOutletId(request('mode'), request('outlet_id'));

        $carts = PointOfSaleCart::with('item')
            ->where('admin_id', auth()->user()->id)
            ->where('outlet_id', $outletId)
            ->latest()
            ->get();

        // Sinkronkan harga keranjang dengan harga terbaru dari database item
        foreach ($carts as $cart) {
            if ($cart->item && $cart->price != $cart->item->selling_price) {
                $cart->price = $cart->item->selling_price;
                $cart->total = $cart->quantity * $cart->price;
                $cart->save();
            }
        }

        return $this->postSuccessResponse("Data keranjang berhasil diambil", $carts);
    }

    public function addItemToCart(Request $request)
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            $admin = auth()->user();
            $outletId = $admin->getEffectiveOutletId(request('mode'), request('outlet_id'));

            $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
            $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

            $item = Item::where('code', $request->code)
                ->when($outletId, function($q) use ($outletId, $koperasiId) {
                    $q->where(function($query) use ($outletId, $koperasiId) {
                        $query->where('outlet_id', $outletId);
                        if ($outletId === $koperasiId) {
                            $query->orWhereNull('outlet_id');
                        }
                    });
                })
                ->lockForUpdate()->first();
            if (!$item) {
                return $this->failedResponse("Barang tidak ditemukan", null);
            }

            $cart = PointOfSaleCart::where('item_id', $item->id)->where('admin_id', auth()->user()->id)->lockForUpdate()->first();
            if ($cart) {
                $cart->quantity += $request->quantity;
                $cart->total = $cart->quantity * $cart->price;
                $cart->save();
            } else {
                $cart = new PointOfSaleCart();
                $cart->admin_id = auth()->user()->id;
                $cart->outlet_id = $outletId;
                $cart->item_id = $item->id;
                $cart->quantity = $request->quantity;
                $cart->price = $item->selling_price;
                $cart->total = $cart->quantity * $cart->price;
                $cart->save();
            }

            // Validasi stok sebelum pengurangan — cegah stok negatif
            if ($item->stock < $request->quantity) {
                DB::rollback();
                return $this->failedResponse(
                    "Stok tidak mencukupi. Tersedia: {$item->stock}, Diminta: {$request->quantity}",
                    null
                );
            }

            // Update stock on item
            $item->stock -= $request->quantity;
            $item->save();

            // Commit transaction
            DB::commit();

            return $this->postSuccessResponse("Barang berhasil ditambahkan ke keranjang", $cart);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollback();

            // Log the error
            Log::error('Error occurred while adding item to cart: ' . $e->getMessage());

            // Return failure response
            return $this->failedResponse("Terjadi kesalahan saat menambahkan barang ke keranjang", null);
        }
    }


    public function deleteCart(Request $request)
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            // Find the cart
            $cart = PointOfSaleCart::where('id', $request->id)
                ->where('admin_id', auth()->user()->id)
                ->lockForUpdate() // Lock the row for update to prevent race conditions
                ->first();

            if (!$cart) {
                // Rollback transaction if cart is not found
                DB::rollback();
                return $this->failedResponse("Data keranjang tidak ditemukan", null);
            }

            // Update stock on item
            $item = Item::find($cart->item_id);
            $item->stock += $cart->quantity;
            $item->save();

            // Delete the cart
            $cart->delete();

            // Commit transaction
            DB::commit();

            return $this->postSuccessResponse("Barang berhasil dihapus dari keranjang", null);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollback();

            // Log the error
            Log::error('Error occurred while deleting cart: ' . $e->getMessage());

            // Return failure response
            return $this->failedResponse("Terjadi kesalahan saat menghapus keranjang", null);
        }
    }


    public function updateCartQuantity(Request $request)
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            $cart = PointOfSaleCart::where('id', $request->id)
                ->where('admin_id', auth()->user()->id)
                ->lockForUpdate() // Lock the row for update to prevent race conditions
                ->first();

            if (!$cart) {
                // Rollback transaction if cart is not found
                DB::rollback();
                return $this->failedResponse("Data keranjang tidak ditemukan", null);
            }

            // Update stock on item
            $item = Item::find($cart->item_id);
            $item->stock += $cart->quantity;
            $item->stock -= $request->quantity;

            // Validasi stok sebelum update — cegah stok negatif
            if ($item->stock < 0) {
                DB::rollback();
                $availableStock = $item->stock + $request->quantity; // revert for display
                return $this->failedResponse(
                    "Stok tidak mencukupi. Tersedia: {$availableStock}, Diminta: {$request->quantity}",
                    null
                );
            }

            $item->save();

            // Update cart quantity and total
            $cart->quantity = $request->quantity;
            $cart->total = $cart->quantity * $cart->price;
            $cart->save();

            // Commit transaction
            DB::commit();

            return $this->postSuccessResponse("Data keranjang berhasil diupdate", $cart);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollback();

            // Log the error
            Log::error('Error occurred while updating cart quantity: ' . $e->getMessage());

            // Return failure response
            return $this->failedResponse("Terjadi kesalahan saat memperbarui jumlah keranjang", null);
        }
    }


    public function getTotalPrice()
    {
        $admin = auth()->user();
        $outletId = $admin->getEffectiveOutletId(request('mode'), request('outlet_id'));
        $total = PointOfSaleCart::where('admin_id', auth()->user()->id)->where('outlet_id', $outletId)->sum('total');
        return $this->getSuccessResponse($total);
    }

    public function deleteAllCart()
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            $admin = auth()->user();
            $outletId = $admin->getEffectiveOutletId(request('mode'), request('outlet_id'));

            // Retrieve all carts belonging to the authenticated user and specific outlet
            $carts = PointOfSaleCart::where('admin_id', auth()->user()->id)->where('outlet_id', $outletId)->get();

            // Update stock on items and delete carts
            foreach ($carts as $cart) {
                $item = Item::find($cart->item_id);
                if ($item) {
                    $item->stock += $cart->quantity;
                    $item->save();
                }
            }

            // Delete all carts
            PointOfSaleCart::where('admin_id', auth()->user()->id)->where('outlet_id', $outletId)->delete();

            // Commit transaction
            DB::commit();

            return $this->postSuccessResponse("Keranjang berhasil dikosongkan", null);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollback();

            // Log the error
            Log::error('Error occurred while deleting all carts: ' . $e->getMessage());

            // Return failure response
            return $this->failedResponse("Terjadi kesalahan saat menghapus semua keranjang", null);
        }
    }

    public function getDailyTransaction()
    {
        $admin = auth()->user();
        $outletId = $admin->getEffectiveOutletId(request('mode'), request('outlet_id'));

        // Ambil transaksi yang sesuai dengan admin, outlet, dan tanggal hari ini
        $transactions = PointOfSaleTransaction::whereDate('paid_at', now())
            ->where('admin_id', auth()->user()->id)
            ->where('outlet_id', $outletId)
            ->latest()
            ->get();

        // Map data untuk menambahkan format tambahan
        $transactions = $transactions->map(function ($transaction, $index) {
            return [
                'no' => $index + 1, // Nomor urut
                'pay_amount' => $transaction->pay_amount
                    ? 'Rp. ' . number_format($transaction->pay_amount, 0, ',', '.')
                    : 'Rp. 0',
                'student' => $transaction->student ? $transaction->student->name : 'Umum',
                'items' => $transaction->pointOfSaleTransactionDetails->map(function ($detail) {
                    return [
                        'name' => $detail->item->name,
                        'qty' => $detail->quantity,
                        'price' => 'Rp. ' . number_format($detail->price, 0, ',', '.'),
                    ];
                }),
                'paid_at' => $transaction->paid_at->format('d-m-Y H:i:s'),
            ];
        });

        // Mengembalikan respons dengan pesan sukses dan data yang diformat
        return $this->postSuccessResponse("Data transaksi harian berhasil diambil", $transactions);
    }
}
