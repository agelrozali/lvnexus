<?php

namespace UkrSolution\BarcodesDigital\Generators;

use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;

class ZATCA
{

    public function generateLineBarcodeData($post, $defaultValue, $sellerName, $vatNumber)
    {
        try {
            if (!$post) return $defaultValue;

            if ($post->post_type !== "shop_order") return $defaultValue;

            $order = \wc_get_order($post->ID);

            if (!$order) return $defaultValue;

            $invoiceTotal = (float)$order->get_total();
            $invoiceTax = (float)$order->get_total_tax();
            $invoiceDate = str_replace("T", "", $post->post_date) . "Z";

            $generatedString = $this->generateBase64($sellerName, $vatNumber, $invoiceDate, $invoiceTotal, $invoiceTax);

            return $generatedString;
        } catch (\Throwable $th) {
            return $defaultValue;
        }
    }

    public function generatePreviewLineBarcodeData($sellerName, $vatNumber)
    {
        try {
            $dt = new \DateTime("now");

            $invoiceTotal = 9.99;
            $invoiceTax = 0.9;
            $invoiceDate = str_replace("T", "", $dt->format("Y-m-d H:i:s")) . "Z";

            $generatedString = $this->generateBase64($sellerName, $vatNumber, $invoiceDate, $invoiceTotal, $invoiceTax);

            return $generatedString;
        } catch (\Throwable $th) {
            return "";
        }
    }

    private function generateBase64($seller, $taxNumber, $invoiceDate, $invoiceTotal, $invoiceTax)
    {
        try {
            $generatedString = GenerateQrCode::fromArray([
                new Seller($seller), 
                new TaxNumber($taxNumber), 
                new InvoiceDate($invoiceDate), 
                new InvoiceTotalAmount($invoiceTotal), 
                new InvoiceTaxAmount($invoiceTax) 
            ])->toBase64(); 

            return $generatedString;
        } catch (\Throwable $th) {
            return "";
        }
    }
}
