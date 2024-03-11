<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\Student;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\PointOfSaleCart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\PointOfSaleTransaction;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        // Use database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            $adminId = auth()->user()->id;

            // Get cart data
            $carts = PointOfSaleCart::where('admin_id', $adminId)->get();
            if ($carts->isEmpty()) {
                return redirect()->back()->with('error', 'Keranjang masih kosong');
            }

            // Calculate total
            $total = $carts->sum('total');

            // Get student data
            $student = Student::findOrFail($request->student_id);
            if ($student->saldo < $total) {
                return redirect()->back()->with('error', 'Saldo siswa tidak mencukupi');
            }

            // check if student is blocked
            if ($student->is_blocked) {
                return redirect()->back()->with('error', 'Saldo Siswa Masih Diblokir');
            }

            // Check if student has reached the daily limit
            $totalThisDay = PointOfSaleTransaction::where('student_id', $student->id)
                ->whereDate('paid_at', now())
                ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
                ->sum('pay_amount') ?? 0;

            if ($student->daily_limit < $totalThisDay + $total) {
                return redirect()->back()->with('error', 'Maaf, Siswa telah mencapai batas transaksi harian');
            }

            // Deduct student's balance
            $student->saldo -= $total;
            $student->save();

            // Add history saldo
            $history = SaldoHistory::create([
                'student_id' => $student->id,
                'type' => 'OUT',
                'amount' => $total,
                'description' => 'Pembayaran Pembelian Barang Rp. ' . number_format($total, 0, ',', '.'),
                'status' => 'SUCCESS',
                'usage' => SaldoHistory::USAGE_POS,
            ]);

            // Save transaction data
            $transaction = PointOfSaleTransaction::create([
                'student_id' => $request->student_id,
                'admin_id' => $adminId,
                'payment_code' => 'POS-CHT-' . time() . uniqid(4),
                'pay_amount' => $total,
                'paid_at' => now(),
                'status' => PointOfSaleTransaction::STATUS_SUCCESS,
                'saldo_history_id' => $history->id,
            ]);

            // Add transaction details
            $transactionDetails = $carts->map(function ($cart) {
                return [
                    'item_id' => $cart->item_id,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                    'total' => $cart->total,
                ];
            })->toArray();

            $transaction->pointOfSaleTransactionDetails()->createMany($transactionDetails);

            // Delete cart
            $carts->each->delete();

            // Commit the transaction
            DB::commit();

            return redirect()->route('order-item.index')->with('success', 'Transaksi berhasil');
        } catch (\Exception $e) {
            // If an exception occurs, rollback the transaction
            DB::rollback();
            return redirect()->back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }


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
        $student = Student::with('classroom')->where('barcode', $barcode)->first();
        if (!$student) {
            return $this->postSuccessResponse("Data siswa tidak ditemukan", null);
        }
        return $this->postSuccessResponse("Data siswa ditemukan", $student);
    }

    public function getCartData()
    {
        $carts = PointOfSaleCart::with('item')->where('admin_id', auth()->user()->id)->latest()->get();
        return $this->postSuccessResponse("Data keranjang berhasil diambil", $carts);
    }

    public function addItemToCart(Request $request)
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            $item = Item::where('code', $request->code)->lockForUpdate()->first();
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
                $cart->item_id = $item->id;
                $cart->quantity = $request->quantity;
                $cart->price = $item->price;
                $cart->total = $cart->quantity * $cart->price;
                $cart->save();
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
        $total = PointOfSaleCart::where('admin_id', auth()->user()->id)->sum('total');
        $total = "Rp. " . number_format($total, 0, ',', '.');
        return $this->getSuccessResponse($total);
    }

    public function deleteAllCart()
    {
        // Begin transaction
        DB::beginTransaction();

        try {
            // Retrieve all carts belonging to the authenticated user
            $carts = PointOfSaleCart::where('admin_id', auth()->user()->id)->get();

            // Update stock on items and delete carts
            foreach ($carts as $cart) {
                $item = Item::find($cart->item_id);
                if ($item) {
                    $item->stock += $cart->quantity;
                    $item->save();
                }
            }

            // Delete all carts
            PointOfSaleCart::where('admin_id', auth()->user()->id)->delete();

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
}
