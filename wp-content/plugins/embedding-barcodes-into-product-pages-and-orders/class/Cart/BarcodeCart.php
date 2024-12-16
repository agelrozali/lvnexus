<?php

namespace UkrSolution\BarcodesDigital\Cart;

class BarcodeCart
{
    public function __construct()
    {
    }

    public function addToCart($postId)
    {
        try {
            $product = \get_product($postId);

            if (!$product) return;

            if (is_null(WC()->cart)) {
                wc_load_cart();
            }

            WC()->session->set('wc_notices', array());
            WC()->session->set_customer_session_cookie(true);

            WC()->cart->get_cart_contents_count();

            if ($product->get_type() === "variation") {
                WC()->cart->add_to_cart($product->parent_id, 1, $postId, \wc_get_product_variation_attributes($postId));
            } else {
                $cartId = WC()->cart->generate_cart_id($postId);

                if (WC()->cart->find_product_in_cart($cartId)) {
                    $item = WC()->cart->get_cart_item($cartId);
                    $quantity = (isset($item["quantity"]) && $item["quantity"]) ? $item["quantity"] + 1 : 1;
                    WC()->cart->set_quantity($cartId, $quantity);
                } else {
                    WC()->cart->add_to_cart($postId);
                }
            }

            WC()->cart->calculate_totals();

            if (\wp_redirect(\wc_get_cart_url())) exit;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
