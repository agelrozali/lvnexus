<?php
$staticImageUrl = UkrSolution\BarcodesDigital\Helpers\Variables::$A4B_PLUGIN_BASE_URL . "assets/img/amazon-200x113.jpg";

$adaptiveTemplate = '<div data-adaptive="adaptive" style="display:flex;align-items:center;height:100%;width:100%;text-align:center;">
  <div data-barcode="barcode" style="display: none;margin: 2px 0;">
      <img style="width: 100%;position: absolute;bottom: 0;left: 0;" src="[barcode_img_url]" class="barcode-basic-image"/>
  </div>
  <div data-lines="lines">
      <div style="max-height: 17.6px; overflow: hidden; font-size: 16px;" class="barcode-basic-line1" >
          [line1]
      </div>
      <div style="font-size: 16px; max-height: 17.6px;" class="barcode-basic-line2">
          [line2]
      </div>
      <div style="height:33%;overflow:hidden;margin: 2px 0;position: relative;">
          <img style="width: 100%;position: absolute;bottom: 0;left: 0;" src="[barcode_img_url]" class="barcode-basic-image" />
      </div>
      <div style="font-size: 16px; max-height: 17.6px;overflow: hidden;" class="barcode-basic-line3">
          [line3]
      </div>
      <div style="font-size: 16px; max-height: 17px;overflow: hidden;" class="barcode-basic-line4">
          [line4]
      </div>
  </div>
</div>';

return array(

  array(
    'name' => 'Barcode',
    'slug' => 'default-1',
    'template' => '[barcode_img_url width=400 height=112]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 65,
    'width' => 220,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'C128',
  ),
  array(
    'name' => 'Barcode + 1 line',
    'slug' => 'default-2',
    'template' => '[barcode_img_url width=400 height=112]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 75,
    'width' => 220,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'C128',
  ),
  array(
    'name' => 'Barcode + 2 lines',
    'slug' => 'default-3',
    'template' => '[barcode_img_url width=400 height=112 y=32]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 85,
    'width' => 220,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'C128',
  ),
  array(
    'name' => 'Barcode + 4 lines',
    'slug' => 'default-4',
    'template' => '[barcode_img_url width=400 height=112 y=64]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 110,
    'width' => 240,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'C128',
  ),
  array(
    'name' => 'QRCode',
    'slug' => 'default-5',
    'template' => '[barcode_img_url width=408 height=408]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 100,
    'width' => 100,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'QRCODE',
  ),
  array(
    'name' => 'QRCode + 2 lines',
    'slug' => 'default-6',
    'template' => '[barcode_img_url width=320 height=320 y=85]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 153,
    'width' => 100,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'QRCODE',
  ),
  array(
    'name' => 'QRCode + 4 lines',
    'slug' => 'default-7',
    'template' => '[barcode_img_url width=264 height=264]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 100,
    'width' => 265,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'QRCODE',
  ),
  array(
    'name' => 'EAN/UPC - Tall',
    'slug' => 'default-8',
    'template' => '[barcode_img_url width=360 height=251]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 153,
    'width' => 220,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'EAN13',
  ),
  array(
    'name' => 'EAN/UPC - Short',
    'slug' => 'default-9',
    'template' => '[barcode_img_url width=360 height=251]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 83,
    'width' => 220,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'EAN13',
  ),
  array(
    'name' => 'ZATCA',
    'slug' => 'zatca-qr',
    'template' => '[barcode_img_url width=408 height=408]',
    'is_default' => 1,
    'is_base' => 0,
    'height' => 100,
    'width' => 100,
    'uol_id' => 3,
    'base_padding_uol' => 0,
    'barcode_type' => 'QRCODE',
  ),
);
