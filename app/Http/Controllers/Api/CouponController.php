<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Coupon::class);

        $coupons = Coupon::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $coupons
        ]);
    }

    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);

        $this->authorize('view', $coupon);

        return response()->json([
            'status' => 'success',
            'data' => $coupon
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Coupon::class);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date|after:today',
            'active' => 'boolean',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::create($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Cupom criado com sucesso',
            'data' => $coupon
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $this->authorize('update', $coupon);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:coupons,code,' . $id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_value' => 'required|numeric|min:0',
            'expires_at' => 'nullable|date|after:today',
            'active' => 'boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'used_count' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon->update($validator->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Cupom atualizado com sucesso',
            'data' => $coupon
        ]);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);

        $this->authorize('delete', $coupon);

        $coupon->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Cupom excluído com sucesso'
        ]);
    }

    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:coupons,code',
            'subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cupom não encontrado'
            ], 404);
        }

        if (!$coupon->active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este cupom não está ativo'
            ], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este cupom expirou'
            ], 422);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Este cupom atingiu o limite de uso'
            ], 422);
        }

        if ($request->subtotal < $coupon->min_value) {
            return response()->json([
                'status' => 'error',
                'message' => 'O valor mínimo para este cupom é R$ ' . number_format($coupon->min_value, 2, ',', '.'),
                'min_value' => $coupon->min_value
            ], 422);
        }

        $discount = 0;
        if ($coupon->type === 'percentage') {
            $discount = $request->subtotal * ($coupon->value / 100);
        } else {
            $discount = min($coupon->value, $request->subtotal);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cupom válido',
            'data' => [
                'coupon' => $coupon,
                'discount' => $discount,
                'discount_formatted' => 'R$ ' . number_format($discount, 2, ',', '.')
            ]
        ]);
    }
}
