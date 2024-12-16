<?php

namespace UkrSolution\BarcodesDigital;

use UkrSolution\BarcodesDigital\Cart\BarcodeCart;

class Frontend
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;

        add_action('init', array($this, "parseURL"));
    }

    public function parseURL()
    {
        if (preg_match('/\/us-barcodes-print-add-to-cart\?(.*?)?$/', $_SERVER["REQUEST_URI"], $m)) {
            if (isset($_GET["id"])) {
                $postId = trim(sanitize_text_field($_GET["id"]));

                if ($postId) {
                    $barcodeCart = new BarcodeCart();
                    $barcodeCart->addToCart($postId);
                }
            }
        }
    }
}
