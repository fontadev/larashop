<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['validate']);
    }

    public function index()
    {
        $coupons = Coupon::latest()->get();
        return view('modules.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('modules.coupons.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date|after:today',
        ]);

        Coupon::create($validatedData);

        return redirect()->route('coupons.index')
            ->with('success', 'Cupom criado com sucesso!');
    }

    public function edit(Coupon $coupon)
    {
        return view('modules.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $coupon->update($validatedData);

        return redirect()->route('coupons.index')
            ->with('success', 'Cupom atualizado com sucesso!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('coupons.index')
            ->with('success', 'Cupom excluído com sucesso!');
    }

    public function check(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $validatedData['code'])->first();

        if (!$coupon || !$coupon->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Cupom inválido ou expirado.',
            ]);
        }

        if ($validatedData['subtotal'] < $coupon->min_value) {
            return response()->json([
                'valid' => false,
                'message' => "Valor mínimo para este cupom: R$ {$coupon->min_value}",
            ]);
        }

        $discount = $coupon->calculateDiscount($validatedData['subtotal']);

        return response()->json([
            'valid' => true,
            'discount' => $discount,
            'message' => 'Cupom válido!',
        ]);
    }
}
