<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Stock;
use Illuminate\Session\SessionManager;

class CartService
{
    protected $session;
    protected $coupon;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    public function getContent()
    {
        return $this->session->get('cart', []);
    }

    public function add($productId, $quantity = 1, $variationId = null)
    {
        $cart = $this->getContent();
        $product = Product::findOrFail($productId);

        $stock = null;
        if ($variationId) {
            $stock = Stock::where('product_id', $productId)
                ->where('product_variation_id', $variationId)
                ->first();
        } else {
            $stock = Stock::where('product_id', $productId)
                ->whereNull('product_variation_id')
                ->first();
        }

        if (!$stock || $stock->quantity < $quantity) {
            return false;
        }

        $itemId = $variationId ? $productId . '-' . $variationId : $productId;

        if (isset($cart[$itemId])) {
            $newQuantity = $cart[$itemId]['quantity'] + $quantity;
            if ($stock->quantity < $newQuantity) {
                return false;
            }
            $cart[$itemId]['quantity'] = $newQuantity;
        } else {
            $variation = null;
            $variationName = null;

            if ($variationId) {
                $variation = ProductVariation::find($variationId);
                $variationName = $variation ? $variation->name : null;
            }

            $cart[$itemId] = [
                'id' => $productId,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'variation_id' => $variationId,
                'variation_name' => $variationName
            ];
        }

        $this->session->put('cart', $cart);
        return true;
    }

    public function update($itemId, $quantity)
    {
        $cart = $this->getContent();

        if (!isset($cart[$itemId])) {
            return false;
        }

        $productId = $cart[$itemId]['id'];
        $variationId = $cart[$itemId]['variation_id'];

        $stock = null;
        if ($variationId) {
            $stock = Stock::where('product_id', $productId)
                ->where('product_variation_id', $variationId)
                ->first();
        } else {
            $stock = Stock::where('product_id', $productId)
                ->whereNull('product_variation_id')
                ->first();
        }

        if (!$stock || $stock->quantity < $quantity) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->remove($itemId);
        }

        $cart[$itemId]['quantity'] = $quantity;
        $this->session->put('cart', $cart);

        return true;
    }

    public function remove($itemId)
    {
        $cart = $this->getContent();

        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            $this->session->put('cart', $cart);
        }

        return true;
    }

    public function clear()
    {
        $this->session->forget('cart');
        $this->session->forget('coupon');
        $this->coupon = null;
    }

    public function getSubtotal()
    {
        $subtotal = 0;
        $cart = $this->getContent();

        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return $subtotal;
    }

    public function getShipping()
    {
        $subtotal = $this->getSubtotal();

        if ($subtotal >= 200) {
            return 0;
        } elseif ($subtotal >= 52 && $subtotal <= 166.59) {
            return 15;
        }

        return 20;
    }

    public function applyCoupon($code)
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->isValid()) {
            $this->session->forget('coupon');
            $this->coupon = null;
            return false;
        }

        $subtotal = $this->getSubtotal();
        if ($subtotal < $coupon->min_value) {
            $this->session->forget('coupon');
            $this->coupon = null;
            return false;
        }

        $this->session->put('coupon', $code);
        $this->coupon = $coupon;
        return true;
    }

    public function getDiscount()
    {
        $couponCode = $this->session->get('coupon');

        if (!$couponCode) {
            return 0;
        }

        if ($this->coupon === null) {
            $this->coupon = Coupon::where('code', $couponCode)->first();
        }

        if (!$this->coupon || !$this->coupon->isValid()) {
            $this->session->forget('coupon');
            $this->coupon = null;
            return 0;
        }

        return $this->coupon->calculateDiscount($this->getSubtotal());
    }

    public function getTotal()
    {
        return $this->getSubtotal() + $this->getShipping() - $this->getDiscount();
    }

    public function getCouponCode()
    {
        return $this->session->get('coupon');
    }
}
