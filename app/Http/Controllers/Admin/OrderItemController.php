<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\PointOfSaleCart;
use App\Http\Controllers\Controller;

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
        //
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
        $item = Item::where('code', $request->code)->first();
        if (!$item) {
            return $this->failedResponse("Barang tidak ditemukan", null);
        }
        $cart = PointOfSaleCart::where('item_id', $item->id)->where('admin_id', auth()->user()->id)->first();
        if ($cart) {
            $cart->quantity += $request->quantity;
            $cart->total = $cart->quantity * $cart->price;
            $cart->save();
            return $this->postSuccessResponse("Barang berhasil ditambahkan ke keranjang", $cart);
        }
        $cart = new PointOfSaleCart();
        $cart->admin_id = auth()->user()->id;
        $cart->item_id = $item->id;
        $cart->quantity = $request->quantity;
        $cart->price = $item->price;
        $cart->total = $cart->quantity * $cart->price;
        $cart->save();
        return $this->postSuccessResponse("Barang berhasil ditambahkan ke keranjang", $cart);
    }

    public function deleteCart(Request $request)
    {
        $cart = PointOfSaleCart::where('id', $request->id)->where('admin_id', auth()->user()->id)->first();
        if (!$cart) {
            return $this->failedResponse("Data keranjang tidak ditemukan", null);
        }
        $cart->delete();
        return $this->postSuccessResponse("Barang berhasil dihapus dari keranjang", null);
    }

    public function updateCartQuantity(Request $request)
    {
        $cart = PointOfSaleCart::where('id', $request->id)->where('admin_id', auth()->user()->id)->first();
        if (!$cart) {
            return $this->failedResponse("Data keranjang tidak ditemukan", null);
        }
        $cart->quantity = $request->quantity;
        $cart->total = $cart->quantity * $cart->price;
        $cart->save();
        return $this->postSuccessResponse("Data keranjang berhasil diupdate", $cart);
    }

    public function getTotalPrice()
    {
        $total = PointOfSaleCart::where('admin_id', auth()->user()->id)->sum('total');
        $total = "Rp. " . number_format($total, 0, ',', '.');
        return $this->getSuccessResponse($total);
    }
}
