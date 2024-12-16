<div id="add-multi-origin" class="modal">
	<div class="wc-backbone-modal">
			<div class="wc-backbone-modal-content" style="margin-top: 32px;">
				<section class="wc-backbone-modal-main" role="main">
					<article>
						<div class="">
							<hr style="margin: 20px 0px;">
							<form action="" method="post">
								<table width="100%">
									<tr>
										<td><label for="multiorigin_shop_name">Nama Toko</label></td>
										<td><input id="multiorigin_shop_name" placeholder="Masukan nama toko" name="multiorigin_shop_name" value="" style="width:100%;"/></td>
									<tr>
									<tr>
										<td><label for="multiorigin_address">Alamat</label></td>
										<td> 
											<textarea class="input-text wide-input" placeholder="Masukan Alamat" id="multiorigin_address" name="multiorigin_address" style="width: 100%;height: 55px;"></textarea>
										</td>
									<tr>										
									<tr>
										<td><label for="multiorigin_province">Provinsi</label></td>
										<td>
											<select id="multiorigin_province">
											<?php
           $list_province = [
               "Bali" => "IDNP1",
               "Bangka Belitung" => "IDNP2",
               "Banten" => "IDNP3",
               "Bengkulu" => "IDNP4",
               "DI Yogyakarta" => "IDNP5",
               "DKI Jakarta" => "IDNP6",
               "Gorontalo" => "IDNP7",
               "Jambi" => "IDNP8",
               "Jawa Barat" => "IDNP9",
               "Jawa Tengah" => "IDNP10",
               "Jawa Timur" => "IDNP11",
               "Kalimantan Barat" => "IDNP12",
               "Kalimantan Selatan" => "IDNP13",
               "Kalimantan Tengah" => "IDNP14",
               "Kalimantan Timur" => "IDNP15",
               "Kalimantan Utara" => "IDNP16",
               "Kepulauan Riau" => "IDNP17",
               "Lampung" => "IDNP18",
               "Maluku" => "IDNP19",
               "Maluku Utara" => "IDNP20",
               "Nanggroe Aceh Darussalam (NAD)" => "IDNP21",
               "Nusa Tenggara Barat (NTB)" => "IDNP22",
               "Nusa Tenggara Timur (NTT)" => "IDNP23",
               "Papua" => "IDNP24",
               "Papua Barat" => "IDNP25",
               "Riau" => "IDNP26",
               "Sulawesi Barat" => "IDNP27",
               "Sulawesi Selatan" => "IDNP28",
               "Sulawesi Tengah" => "IDNP29",
               "Sulawesi Tenggara" => "IDNP30",
               "Sulawesi Utara" => "IDNP31",
               "Sumatera Barat" => "IDNP32",
               "Sumatera Selatan" => "IDNP33",
               "Sumatera Utara" => "IDNP34",
           ];
           foreach ($list_province as $province_name => $province_code) {
               echo '<option value="' . $province_code . '">' . $province_name . "</option>";
           }
           ?>
											</select>
										</td>
									<tr>
									<tr>
										<td><label for="multiorigin_zipcode">Zipcode</label></td>
										<td><input id="multiorigin_zipcode" value="" style="width: 15%;"/></td>
									<tr>																			
								</table>
								<div>
									<br>
									<form id="mapSearchForm" style="margin-top: 10px;font-size:13px;">
									<input style="width: 100%" type="text" id="placeSearch" placeholder="Cari titik lokasi alamat"> 
									</form>
									<div id="map" class="map-order-review"></div>
								</div>								
							</form>
						</div>
					</article>
					<footer>
						<div class="inner">
						<div style="width: 10%;float:left;">
							<a id="close-modal-add-multi-origin"class="button button-danger button-large"><?php esc_html_e("Tutup", "woocommerce"); ?></a>
						</div>
						<div>
							<a id="save-multiorigin"class="button button-primary button-large"><?php esc_html_e("Simpan", "woocommerce"); ?></a>
						</div>						
						</div>
					</footer>
				</section>
			</div>
		</div>
		<div class="wc-backbone-modal-backdrop modal-close"></div>	
	</div>
</div>

<script>
	jQuery(function($) {
		$("#save-multiorigin").on('click', function() {
			var multiorigin_shop_name = $("#multiorigin_shop_name").val();
			var multiorigin_address = $("#multiorigin_address").val();
			var multiorigin_province = $("#multiorigin_province").val();
			var multiorigin_zipcode = $("#multiorigin_zipcode").val();
			if(multiorigin_shop_name.length === 0){
				return alert('Nama shop harus diisi')
			}
			if(multiorigin_address.length === 0){
				return alert('Alamat harus diisi')
			}
			if(multiorigin_province.length === 0){
				return alert('Provinsi harus diisi')
			}
			if(multiorigin_zipcode.length === 0){
				return alert('Zipcode harus diisi')
			}else if(multiorigin_zipcode.length < 5){
				return alert('Zipcode tidak 5 digit')
			}							
			window.onbeforeunload = null;
			$("#new_multiorigin_shop_name").val(multiorigin_shop_name);
			$("#new_multiorigin_address").val(multiorigin_address);
			$("#new_multiorigin_province").val(multiorigin_province);
			$("#new_multiorigin_zipcode").val(multiorigin_zipcode);
			$('#mainform').submit();
		})

		if (!phpVars) {
      		phpVars = {};
		}
		var marker = null;
		var enc = function(s, b) { var w = ""; for (var i = 0; i < s.length; i++) { w += String.fromCharCode(s.charCodeAt(i) ^ b) } return unescape(w) };
		var apiKey = enc(phpVars.apiKey,window.location.host.replace("www.", "").length) || '';
		var originPosition = phpVars.origin_position || '';
		var input = document.getElementById("placeSearch");
		if(input !== null) {
			$.getScript(`https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`, function() {
				var myLatLng = {lat: -6.1753871, lng: 106.8249641};
				if(originPosition.length > 0){
					var originPositionSplit = originPosition.split(",")
					myLatLng = {lat: parseFloat(originPositionSplit[0]), lng: parseFloat(originPositionSplit[1])};
				}
				
				var map = new google.maps.Map(
				document.getElementById('map'), {
					zoom: 15, 
					center: myLatLng,
					disableDefaultUI: true,
					fullscreenControl: true
				}
				);
				marker = new google.maps.Marker({
					position: myLatLng,
					map: map,
					title: 'Location',
				})
		
				map.addListener('click', function(mapsMouseEvent) {
					var selectedPosition = mapsMouseEvent.latLng
					setPosition(marker, map, selectedPosition);
				});
				var input = document.getElementById("placeSearch");
				var autocomplete = new google.maps.places.Autocomplete(input);
				autocomplete.setComponentRestrictions({"country": ["id"]});
				/* Add event when user click searchbox autocomplete */
				autocomplete.addListener("place_changed", function() {
					var place = autocomplete.getPlace();
					if (!place.geometry) {
						/*User entered the name of a Place that was not suggested and
						pressed the Enter key, or the Place Details request failed.*/
						return;
					}
					myLatLng = {
						lat: place.geometry.location.lat(),
						lng: place.geometry.location.lng()
					};
					map.setCenter(place.geometry.location);
					marker.setPosition( place.geometry.location );
					marker.setMap(map);
					$('#new_multiorigin_position').val(place.geometry.location.lat() + ',' + place.geometry.location.lng())
				});
			});
		}
		function setPosition(marker, map, location) {
			marker.setPosition(location);
			marker.setMap(map);
				if($('#position-input').length) {
				$('#position-input').val(location.lat() + ',' + location.lng())
				return;
			}
		}
	})
</script>
