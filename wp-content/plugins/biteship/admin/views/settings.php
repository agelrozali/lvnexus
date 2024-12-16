<h1>Biteship</h1>
<p>Gunakan Biteship untuk mengecek Ongkos Kirim dan Penjemputan Barang. Cek panduan kami <a target="_blank" rel="noopener noreferrer" href="https://help.biteship.com/hc/id/sections/9968775316761-WooCommerce">disini</a></p>
<table class="form-table">
	<?php wp_nonce_field("biteship-settings", "biteship-nonce"); ?>
  <?php $this->generate_settings_html(); ?>
  <tr>
    <th>
      <label><?php echo __("Nama Toko", "biteship"); ?></label>
    </th>
    <td>
      <span>
        <input type="text" value="<?php echo esc_html($options["store_name"]); ?>" name="store_name" placeholder="Contoh: Toko Serba Ada"/>
      </span>
    </td>
  </tr>
  <tr>
    <th>
      <label><?php echo __("Nama Pengirim", "biteship"); ?></label>
    </th>
    <td>
      <span>
        <input type="text" value="<?php echo esc_html($options["shipper_name"]); ?>" name="shipper_name" placeholder="Contoh: John Doe"/>
      </span>
    </td>
  </tr>
  <tr>
    <th>
      <label><?php echo __("Nomor HP", "biteship"); ?></label>
    </th>
    <td>
      <span>
        <input type="text" value="<?php echo esc_html($options["shipper_phone_no"]); ?>" name="shipper_phone_no" placeholder="Contoh: 081295799021"/>
      </span>
    </td>
  </tr>
  <tr>
    <th>
      <label><?php echo __("Email Pengirim", "biteship"); ?> <abbr class="required">*</abbr></label>
    </th>
    <td>
      <span>
        <input type="text" value="<?php echo esc_html(
            $options["shipper_email"]
        ); ?>" name="shipper_email" placeholder="Contoh: john.doe@gmail.com"  oninvalid="this.setCustomValidity('Email Pengirim belum diisi. Disarankan menggunakan email yang sama seperti di akun Biteship')" required/>
        <p class="description"><?php echo __("Email diharuskan sama seperti saat registrasi di Biteship", "biteship"); ?></p>
      </span>
    </td>
  </tr>
  <tr>
    <?php
    $insurance_selected = "";
    $insurance_disabled = "disabled";
    if ($options["insurance_percentage"] > 0 && $options["insurance_enabled"]) {
        $insurance_selected = "checked='checked'";
        $insurance_disabled = "";
    }
    ?>
    <th>
      <label><?php echo __("Aktifkan Asuransi", "biteship"); ?></label>
    </th>
    <td>
      <div style="margin-top: -10px">
        <label class="switch">
            <input class="shipping-service-checkbox" type="checkbox" name="insurance_checkbox" id="insurance_checkbox" value="true" <?php echo $insurance_selected; ?> />
            <span class="slider round"></span>
        </label>
        <span style="margin-left: 20px;">
          <input type="number" step="0.1" value="<?php echo $options[
              "insurance_percentage"
          ]; ?>" name="insurance_percentage" id="insurance_percentage" style="width: 75px" <?php echo $insurance_disabled; ?>/>
        </span>
        <span style="margin-left: 5px;font-size: 20px;">%</span>
      </div>
    </td>
    <td>
      <legend><?php echo __("Harga dasar biaya asuransi adalah 0.5% dari nilai barang", "biteship"); ?></legend>
    </td>
    <input type="hidden" name="insurance_enabled" value="<?= $options["insurance_enabled"] ?>">
  </tr>
  <tr>
    <?php
    $cod_selected = "";
    $cod_disabled = "disabled";
    if ($options["cod_enabled"]) {
        //sebelumnya ada $options['cod_percentage'] > 0
        $cod_selected = "checked='checked'";
        $cod_disabled = "";
    }
    ?>
    <th>
      <label><?php echo __("Aktifkan COD", "biteship"); ?></label>
    </th>
    <td>
      <div style="margin-top: -10px">
        <label class="switch">
            <input class="shipping-service-checkbox" type="checkbox" name="cod_checkbox" id="cod_checkbox" value="true" <?php echo $cod_selected; ?> />
            <span class="slider round"></span>
        </label>
        <span style="margin-left: 20px;">
          <input type="number" min="0" step="0.1" value="<?php echo $options["cod_percentage"]; ?>" name="cod_percentage" id="cod_percentage" style="width: 75px" <?php echo $cod_disabled; ?>/>
        </span>
        <span style="margin-left: 5px;font-size: 20px;">%</span>
      </div>
      <p class="description"><a target="_blank" rel="noopener noreferrer" href="https://help.biteship.com/hc/id/articles/10615706818841-3-Cara-Aktivasi-Fitur-COD">Klik disini untuk melihat panduan aktivasi COD</a></p>
    </td>
    <td>
      <legend><?php echo __("Harga dasar biaya COD umumnya adalah 4% dari nilai barang", "biteship"); ?></legend>
    </td>
    <input type="hidden" name="insurance_enabled" value="<?= $options["cod_enabled"] ?>">
  </tr>
  <tr>
    <th>
      <label><?php echo __("Berat Bawaan", "biteship"); ?></label>
    </th>
    <td>
      <input type="number" value="<?php echo $options["default_weight"]; ?>" name="default_weight" style="width: 75px"/> 
      <span style="margin-left: 5px;font-size: 16px;"> Kg</span>
    </td>
    <td>
      <legend><?php echo __("Berat barang yang akan digunakan jika suatu produk tidak memiliki bobot", "biteship"); ?></legend>
    </td>
  </tr>
  <tr>
    <th>
      <label><?php echo __("Tipe Checkout", "biteship"); ?></label>
    </th>
    <td>
      <fieldset>
        <?php
        $checkout_options = [
            [
                "key" => "smartsearch",
                "value" => "smartsearch",
                "label" => "Smart Search (Direkomendasikan)",
            ],
            [
                "key" => "dropdown",
                "value" => "dropdown",
                "label" => "Dropdown",
            ],
        ];
        $checkout_type = $options["checkout_type"];
        if (empty($checkout_type)) {
            $checkout_type = "smartsearch";
        }
        foreach ($checkout_options as $opt) {

            $checked = "";
            if ($checkout_type == $opt["value"]) {
                $checked = "checked='checked'";
            }
            ?>
            <div style="display: flex; align-items: center">
              <div>
                <input type="radio" name="checkout_type" id="<?php echo $opt["key"]; ?>" value="<?php echo $opt["value"]; ?>" <?php echo $checked; ?>>
              </div>
              <div style="margin-left: 8px; padding-right: 8px">
                <label for="<?php echo $opt["key"]; ?>"><?php echo __($opt["label"], "biteship"); ?></label>
              </div>
            </div>
            <?php
        }
        ?>
      </fieldset>
      <p class="description"><a target="_blank" rel="noopener noreferrer" href="http://bit.ly/3VLEhMr">Klik disini jika mengalami kendala halaman checkout</a></p>
    </td>
    <td style="vertical-align: top">
      <!-- <legend style="margin-top: 15px;"><font color=red><?php echo __(
          "Pastikan untuk pilihan dropdown, disarankan tidak mengcustom halaman checkout mengunakan plugin lain.",
          "biteship"
      ); ?></font></legend> -->
    </td>    
  </tr>
  <tr>
      <?php
      $tracking_page_isactive = "";
      $tracking_page_isactive_style = "none";
      if ($options["tracking_page_isactive"]) {
          $tracking_page_isactive_style = "block";
          $tracking_page_isactive = "checked='checked'";
      }
      ?>    
    <th>
      <label><?php echo __("Tracking Page (Versi Beta)", "biteship"); ?></label>
    </th>
    <td>
      <div style="margin-top: -10px">
        <br>
        <label class="switch">
            <input class="shipping-service-checkbox" type="checkbox" name="tracking_page_checkbox" id="tracking_page_checkbox" value="true" <?php echo $tracking_page_isactive; ?> />
            <span class="slider round"></span>
        </label>
        <div id="component-tracking-page" style="display: <?= $tracking_page_isactive_style ?>;">
          <br>
          <span style="margin-left: 1px;">
            <input type="text" value="<?php echo esc_html(
                $options["tracking_page_url"]
            ); ?>" placeholder="https://dashboard.biteship.com/track-uniqlo" name="tracking_page_url" id="tracking_page_url"/>
            <input type="hidden" name="tracking_page_isactive" id="tracking_page_isactive" value="<?= $options["tracking_page_isactive"] ?>">
          </span>
        </div>
      </div>
      <div  style="margin-top: 10px;">
          <p class="description"><a target="_blank" rel="noopener noreferrer" href="https://dashboard.biteship.com/tracker">Klik disini untuk salin link tracking page</a></p>
      </div>
    </td>
    <td style="vertical-align: top"><!-- comment --></td>
  </tr>
  <tr>
      <?php
      $multiple_origins_isactive = "";
      $multiple_origins_isactive_style = "none";
      if ($options["multiple_origins_isactive"]) {
          $multiple_origins_isactive_style = "block";
          $multiple_origins_isactive = "checked='checked'";
      }
      ?>    
    <th>
      <label><?php echo __("Multi Origins", "biteship"); ?></label>
    </th>
    <td>
      <div style="margin-top: -10px">
        <br>
        <label class="switch">
            <input class="shipping-service-checkbox" type="checkbox" name="multi_origins_checkbox" id="multi_origins_checkbox" value="true" <?php echo $multiple_origins_isactive; ?> />
            <span class="slider round"></span>
        </label>
        <div id="component-multiorigins-page" style="margin-right:-40px; display: <?= $multiple_origins_isactive_style ?>;">
          <br>
          <?php
          $list_province = [
              "IDNP1" => "Bali",
              "IDNP2" => "Bangka Belitung",
              "IDNP3" => "Banten",
              "IDNP4" => "Bengkulu",
              "IDNP5" => "DI Yogyakarta",
              "IDNP6" => "DKI Jakarta",
              "IDNP7" => "Gorontalo",
              "IDNP8" => "Jambi",
              "IDNP9" => "Jawa Barat",
              "IDNP10" => "Jawa Tengah",
              "IDNP11" => "Jawa Timur",
              "IDNP12" => "Kalimantan Barat",
              "IDNP13" => "Kalimantan Selatan",
              "IDNP14" => "Kalimantan Tengah",
              "IDNP15" => "Kalimantan Timur",
              "IDNP16" => "Kalimantan Utara",
              "IDNP17" => "Kepulauan Riau",
              "IDNP18" => "Lampung",
              "IDNP19" => "Maluku",
              "IDNP20" => "Maluku Utara",
              "IDNP21" => "Nanggroe Aceh Darussalam (NAD)",
              "IDNP22" => "Nusa Tenggara Barat (NTB)",
              "IDNP23" => "Nusa Tenggara Timur (NTT)",
              "IDNP24" => "Papua",
              "IDNP25" => "Papua Barat",
              "IDNP26" => "Riau",
              "IDNP27" => "Sulawesi Barat",
              "IDNP28" => "Sulawesi Selatan",
              "IDNP29" => "Sulawesi Tengah",
              "IDNP30" => "Sulawesi Tenggara",
              "IDNP31" => "Sulawesi Utara",
              "IDNP32" => "Sumatera Barat",
              "IDNP33" => "Sumatera Selatan",
              "IDNP34" => "Sumatera Utara",
          ];
          if (is_array($options["multiple_addresses"])) {
              foreach ($options["multiple_addresses"] as $address) {

                  $id = $address["id"];
                  $html_id = "store_address_" . $id;
                  $position = $address["position"];
                  ?>
          <div>
          <div style="display: flex; justify-content: space-between; align-items: center">
            <div style="display: flex; justify-content: space-between; align-items: center; width:100%; margin-right:15px" class="card">
            <div style="display: flex; align-items: center">
              <div style="margin-left: 8px; padding-right: 8px">
                <label for="<?php echo $html_id; ?>"><?= $address["shopname"] .
    (isset($list_province[$address["province"]]) ? " - " . $list_province[$address["province"]] : "") ?><br><?php echo $address["address"] . " - " . $address["zipcode"]; ?></label>
                <br><br>
                <legend style="font-size: 10px;margin-top: -5px;margin-bottom: 10px;">
                  <?php if ($position != "") { ?>
                  <span><?php echo __("Koordinat terpasang", "biteship"); ?></span><span style="font-size: 12px; color: green" class="dashicons dashicons-yes"></span>
                  <?php } else { ?>
                  <span><?php echo __("Belum ada koordinat", "biteship"); ?></span>
                  <?php } ?>
                </legend>
              </div>
            </div>
            <div style="display: flex">
              <?php if ($position !== "") { ?>
                  <!-- <button class="wp-core-ui button-secondary" style="background-color: transparent; border-color: transparent;" type="button" name="show_store_position" value="<?php echo $position; ?>"><span class="dashicons dashicons-location-alt"></span></button>-->
                  <?php } ?>
              <button class="wp-core-ui button-secondary" style="background-color: transparent; border-color: transparent; color: #c9356e" type="submit" name="remove_multi_origins" value="<?php echo $id; ?>"><span class="dashicons dashicons-trash"></span></button>
            </div>
              </div>
            <div>

            <button class="wp-core-ui button-secondary" style="background-color: transparent; border-color: transparent; color: #c9356e" type="submit" name="default_address" value="<?php echo $id; ?>">
              <label class="switch">
                  <input class="shipping-service-checkbox" type="checkbox" value="<?php echo $id; ?>" <?php if ($options["default_address"] == $id) {
    echo "checked";
} ?> />
                  <span class="slider round"></span>
              </label>
            </button>
            </div>
          </div>
          <?php
              }
          }
          if (count((array) $options["multiple_addresses"]) === 0) {
              echo '
              <div  style="margin-top: 10px;">
                <p class="description">Kamu belum simpan address untuk multiple origin</p>
              </div>';
          }
          ?>
          <br><a id="add-multiple-origin" class="wp-core-ui button-primary" href="#"><?php echo __("Tambah Multi origin", "biteship"); ?></a>
        </div>
        <div>
        </div>
      </div>
    </td>
    <td style="vertical-align: top"><!-- comment --></td>
  </tr> 
  <!-- new origin payload-->
  <input type="hidden" name="new_multiorigin_shop_name" id="new_multiorigin_shop_name" value="">
  <input type="hidden" name="new_multiorigin_address" id="new_multiorigin_address" value="">
  <input type="hidden" name="new_multiorigin_province" id="new_multiorigin_province" value="">
  <input type="hidden" name="new_multiorigin_zipcode" id="new_multiorigin_zipcode" value="">
  <input type="hidden" name="new_multiorigin_position" id="new_multiorigin_position" value="">

  <input type="hidden" name="customer_address_type" id="district_postal_code" value="district_postal_code">
  <input type="hidden" name="map_type" id="modal" value="modal">

  <?php if (!$options["multiple_origins_isactive"]) { ?>

  <!-- Ilyasa Auto Update Order Status Woocommerce -->
  <tr>
    <?php
    $order_status_update = "";
    $order_status_update_style = "none";
    if ($options["order_status_update"]) {
        $order_status_update_style = "block";
        $order_status_update = "checked='checked'";
    }
    ?>
    <th>
      <label><?php echo __("Perbaharui Status WooCommerce Otomatis", "biteship"); ?></label>
    </th>
    <td>
      <div style="margin-top: -10px">
        <br>
        <label class="switch">
            <input class="shipping-service-checkbox" type="checkbox" name="order_status_update_checkbox" id="order_status_update_checkbox" value="true" <?php echo $order_status_update; ?> />
            <span class="slider round"></span>
        </label>
      </div>
    </td>
    <td>
      <legend><?php echo __("Status WooCommerce akan diperbaharui menjadi 'Completed' saat status Biteship 'Delivered'", "biteship"); ?></legend>
    </td>
  </tr>
  <tr>
    <th>
      <label id="origin_address"><?php echo __("Alamat Asal", "biteship"); ?></label>
    </th>
    <td>
      <fieldset>
        <div style="margin-top: 16px; width: 100%">
        <input id="position-input" type="hidden" name="new_position" value="<?php $options["new_position"]; ?>"/>
          <textarea class="input-text wide-input" placeholder="<?php echo __("Address", "biteship"); ?>" id="new-address" name="new_address" style="height: 85px;"><?php echo esc_html(
    $options["new_address"]
); ?></textarea>
        </div>
        <div style="margin-top: 16px">
          <input class="input-text regular-input" placeholder="<?php echo __("Postal Code", "biteship"); ?>" type="text" id="new-zipcode" value="<?php echo esc_html(
    $options["new_zipcode"]
); ?>" name="new_zipcode" style="width: 95px;"/>
        </div>
        <br>
        <div>
            <form id="mapSearchForm" style="margin-top: 10px;font-size:13px;">
              <input style="width: 100%" type="text" id="placeSearch" placeholder="Cari titik lokasi alamat"> 
            </form>
            <div id="map" class="map-order-review"></div>
        </div>
        <div>
          <p class="description">
            <span id="cordinate_point" class="valid"><?= strlen($options["new_position"]) > 0
                ? "Koordinat sudah terpasang sesuai pin point Google Maps terakhir"
                : "Belum ada koordinat" ?></span>
            <?= strlen($options["new_position"]) > 0 ? '<img src="//' . $_SERVER["HTTP_HOST"] . '/wp-content/plugins/biteship/public/images/check.png"></img>' : "" ?>
          </p>
        </div>
        <div style="margin-top: 16px">
          <button type="button" id="add-new-address" class="wp-core-ui button-primary"><?php echo __("Simpan Alamat", "biteship"); ?></button>
        </div>
      </fieldset>
    </td>
    <td style="vertical-align: top">
      <legend style="margin-top: 15px;"><?php echo __(
          "Alamat asal, kode pos dan koordinat harus terisi sebagai acuan untuk mengecek ongkos kirim dan lokasi penjemputan barang.",
          "biteship"
      ); ?></legend>
    </td>
  </tr>
  <?php } ?>
  <tr>
    <th>
      <label><?php echo __("Kunci API", "biteship"); ?></label>
    </th>
    <td>
      <span>
        <textarea class="input-text wide-input" placeholder="Salin dan tempel Kunci API dari website Biteship disini" id="licence" name="licence" style="height: 85px;"><?php echo esc_html(
            $options["licence"]
        ); ?></textarea>
      </span>
        <div id="component-validate-licence">
            <?php if (strlen($options["informationLicence"]["licenceTitle"]) === 0) {
                echo '<p class="description"><a target="_blank" rel="noopener noreferrer" href="https://biteship.com">Dapatkan kunci API Biteship disini</a></p>';
            } ?>
            <div>
                <u><h3 class="description" id="licenceTitle"><?= $options["informationLicence"]["licenceTitle"] ?></h3></u>
                <p class="description" id="licenceInfo"><?= $options["informationLicence"]["licenceInfo"] ?></p>
                <p class="description" id="licenceInfoLink"><?= $options["informationLicence"]["licenceInfoLink"] ?></p>
            </div>
            <div style="margin-top: 16px">
                <button type="button" id="active-licence" class="wp-core-ui button-primary"><?= $options["informationLicence"]["message"] === "success"
                    ? "Ubah Lisensi"
                    : "Aktivasi" ?></button>
            </div>
        </div>
        <div id="component-loading-validate-licence" style="display:none">
            </br></br>
            <div style="float:left;"><img src="<?= "//" . $_SERVER["HTTP_HOST"] . "/wp-content/plugins/biteship/public/images/ui-anim_basic_16x16.gif" ?>" style="height: 45px;"></img></div> 
            <div style="float: right;width: 80%;"><p style="margin-top: 15px;">Mengecek license</p></div>
        </div>
    </td>
  </tr>
	<tr>
		<th>
			<label><?php echo __("Pilih Ekspedisi", "biteship"); ?></label>
		</th>
		<td>
			<fieldset>
        <?php
        $i = 0;
        if (!is_array($companies)) {
            $companies = [];
        }
        if (sizeof($companies) == 0) { ?>
            <p><?php echo __("Aktivasi kunci API untuk melihat pilihan ekspedisi yang tersedia.", "biteship"); ?></p>
            </br>
            <p><?php echo __(
                "Biteship menyediakan kemudahan untuk cek ongkos kirim dan layanan penjemputan paket. Terintegrasi dengan lebih dari 20 ekspedisi dan 75 layanan pengantaran.",
                "biteship"
            ); ?></p>
            <?php }
        foreach ($companies as $company) {

            $i++;
            $selected = "";
            $code = $company["code"];
            $name = $company["name"];
            $first_collapsible = $i == 1 ? "first" : "";
            $last_collapsible = $i == count($companies) ? "last" : "";
            ?>
            <div>
              <button type="button" class="collapsible bg-white <?php echo $first_collapsible; ?>"><?php echo $name; ?></button>
              <div class="collapsible-content bg-white <?php echo $last_collapsible; ?>">
              <?php
              $services = $company["services"];
              foreach ($services as $service) {

                  $service_selected = "";
                  $service_code = $code . "/" . $service["code"];
                  $service_name = $service["name"];
                  $service_description = $service["description"] . " (" . $service["shipment_duration_range"] . " " . $service["shipment_duration_unit"] . ")";
                  if ($this->is_service_checked($service_code, $options["shipping_service_enabled"])) {
                      $service_selected = "checked='checked'";
                  }
                  ?>
                  <div style="margin-left: 32px; padding-top: 8px; padding-bottom: 8px; display: flex; justify-content: space-between;" class="border-bottom">
                    <div>
                      <label for="shipping_service_checkbox_<?php echo $service_code; ?>"><?php echo $service_name; ?></label>
                      <legend style="font-size: 12px"><?php echo $service_description; ?></legend>
                    </div>
                    <label class="switch">
                      <input class="shipping-service-checkbox" type="checkbox" name="shipping_company_checkbox[<?php echo $service_code; ?>]" id="shipping_service_checkbox_<?php echo $service_code; ?>" value="<?php echo $service_name; ?>" <?php echo $service_selected; ?> />
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <?php
              }
              ?>
              </div>
            </div>
            <?php
        }
        ?>
			</fieldset>
		</td>
	</tr>
</table>
