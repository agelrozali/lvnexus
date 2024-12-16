(function ($) {
  "use strict";
  /**
   author: sohay@biteship.com
   github: https://github.com/sohainewbie
   version: 2.2.12
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

  $(function () {
    hideComponent($);
    createComponent($);
    if (!phpVars) {
      phpVars = {};
    }

    var marker = null;
    var enc = function (s, b) {
      var w = "";
      for (var i = 0; i < s.length; i++) {
        w += String.fromCharCode(s.charCodeAt(i) ^ b);
      }
      return unescape(w);
    };
    var apiKey = enc(phpVars.apiKey, window.location.host.replace("www.", "").length) || "";

    var originPosition = phpVars.origin_position || "";
    var input = document.getElementById("placeSearch");
    if (input !== null) {
      $.getScript(`https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places`, function () {
        var myLatLng = { lat: -6.1753871, lng: 106.8249641 };
        if (originPosition.length > 0) {
          var originPositionSplit = originPosition.split(",");
          myLatLng = {
            lat: parseFloat(originPositionSplit[0]),
            lng: parseFloat(originPositionSplit[1]),
          };
        }

        var map = new google.maps.Map(document.getElementById("map"), {
          zoom: 15,
          center: myLatLng,
          disableDefaultUI: true,
          fullscreenControl: true,
        });
        marker = new google.maps.Marker({
          position: myLatLng,
          map: map,
          title: "Location",
        });

        map.addListener("click", function (mapsMouseEvent) {
          var selectedPosition = mapsMouseEvent.latLng;
          setPosition(marker, map, selectedPosition);
        });
        var input = document.getElementById("placeSearch");
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.setComponentRestrictions({ country: ["id"] });
        /* Add event when user click searchbox autocomplete */
        autocomplete.addListener("place_changed", function () {
          var place = autocomplete.getPlace();
          if (!place.geometry) {
            /*User entered the name of a Place that was not suggested and
                pressed the Enter key, or the Place Details request failed.*/
            return;
          }
          myLatLng = {
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng(),
          };
          map.setCenter(place.geometry.location);
          marker.setPosition(place.geometry.location);
          marker.setMap(map);
          $("#position-input").val(place.geometry.location.lat() + "," + place.geometry.location.lng());
        });
      });
    }
    var coll = $(".collapsible");
    for (var i = 0; i < coll.length; i++) {
      coll[i].addEventListener("click", function () {
        this.classList.toggle("active");
        var content = this.nextElementSibling;
        if (content.style.maxHeight) {
          content.style.maxHeight = null;
        } else {
          content.style.maxHeight = content.scrollHeight + "px";
        }
      });
    }

    setTimeout(function () {
      if ($("#tracking_page_isactive").val()) {
        $("#component-tracking-page").show();
      } else {
        $("#component-tracking-page").hide();
      }
    }, 2200);

    $("#tracking_page_checkbox").change(function () {
      if (this.checked) {
        $("#component-tracking-page").show();
      } else {
        $("#component-tracking-page").hide();
      }
    });

    $("#multi_origins_checkbox").change(function () {
      if (this.checked) {
        $("#component-multiorigins-page").show();
      } else {
        $("#component-multiorigins-page").hide();
      }
      $("#mainform").submit();
    });

    var saveAddressButton = $("#add-new-address");
    saveAddressButton.on("click", function () {
      var inputNewAddress = $("#new-address");
      var inputNewZipcode = $("#new-zipcode");
      var inputLocation = $("#position-input");
      if (inputNewAddress.val() == "") {
        alert("Alamat belum terpasang. Mohon isi dahulu");
      } else if (inputNewZipcode.val() == "") {
        alert("Kodepos belum terpasang. Mohon isi dahulu");
      } else if (inputLocation.val() == "") {
        alert("Pin Lokasi belum terpasang. Mohon isi dahulu");
      } else {
        window.onbeforeunload = null;
        $("#mainform").submit();
      }
    });

    var checkLicenceButton = $("#active-licence");
    checkLicenceButton.on("click", function () {
      var licence = $("#licence").val();
      if (licence == "") {
        alert("Kodepos atau Alamat belum terpasang. Mohon isi dahulu");
      } else {
        $("#component-validate-licence").hide();
        $("#component-loading-validate-licence").show();
        var baseURL = phpVars.biteshipBaseUrl || "";
        $.ajax({
          url: `${baseURL}/v1/woocommerce/plugins/validate_key`,
          type: "POST",
          data: JSON.stringify({
            licence: licence,
          }),
          dataType: "json",
          contentType: "application/json; charset=utf-8",
          traditional: true,
          success: function (response) {
            if (response.success) {
              if (response.data.type === "woocommerceFree") {
                $("#licenceTitle").text("Paket Starter");
                $("#licenceInfo").text("Kamu dapat menggunakan layanan ekspedisi Reguler");
                $("#licenceInfoLink").html('<a target="_blank" href="https://s.id/1jaGu">Butuh layanan "Next Day", "Instant" atau "Kargo"? Klik disini untuk pelajari</a>');
              } else if (response.data.type === "woocommerceEssentials") {
                $("#licenceTitle").text("Paket Essentials");
                $("#licenceInfo").text("Kamu dapat menggunakan layanan paket Starter dan Next Day");
                $("#licenceInfoLink").html('<a target="_blank" href="https://s.id/1jaGu">Butuh layanan "Instant" atau "Kargo"? Klik disini untuk pelajari</a>');
              } else if (response.data.type === "woocommerceStandard") {
                $("#licenceTitle").text("Paket Standard");
                $("#licenceInfo").text("Kamu dapat menggunakan layanan paket Essentials dan Instan");
                $("#licenceInfoLink").html('<a target="_blank" href="https://s.id/1jaGu">Butuh layanan "Instant" atau "Kargo"? Klik disini untuk pelajari</a>');
              } else if (response.data.type === "woocommercePremium") {
                $("#licenceTitle").text("Paket Premium");
                $("#licenceInfo").text("Kamu dapat menggunakan semua layanan");
              }
              $("#active-licence").text("Update Key");
              $("#licence").css("border", "3px solid #29cb35");
              $("#component-validate-licence").show();
              $("#component-loading-validate-licence").hide();

              window.onbeforeunload = null;
              $("#mainform").submit();
            }
          },
          error: function (msg) {
            let message = "License key tidak valid, mohon input ulang"; //msg.responseJSON.error
            if (msg.responseJSON.code === 40000003) {
              message = "License key belum aktif, silahkan lanjutkan pembayaran di dashboard Biteship";
            }
            alert(message);
            $("#licence").css("border", "3px solid #FF0000");
            $("#component-loading-validate-licence").hide();
            $("#component-validate-licence").show();
            $("#licenceTitle").text("");
            $("#licenceInfo").text("");
            $("#licenceInfoLink").html("");
            $("#active-licence").text("Aktivasi");
          },
        });
      }
    });

    var insuranceCheckbox = $("#insurance_checkbox");
    var insurancePercentageInput = $("#insurance_percentage");
    insuranceCheckbox.change(function () {
      if (this.checked) {
        var defaultInsurancePercentage = 0.5;
        insurancePercentageInput.val(defaultInsurancePercentage);
        insurancePercentageInput.prop("disabled", false);
      } else {
        insurancePercentageInput.val(0);
        insurancePercentageInput.prop("disabled", true);
      }
    });

    var codCheckbox = $("#cod_checkbox");
    var codPercentageInput = $("#cod_percentage");
    codCheckbox.change(function () {
      if (this.checked) {
        var defaultCodPercentage = 4;
        codPercentageInput.val(defaultCodPercentage);
        codPercentageInput.prop("disabled", false);
      } else {
        codPercentageInput.val(0);
        codPercentageInput.prop("disabled", true);
      }
    });

    var states = {};
    $.each($("#billing_state").prop("options"), function (i, opt) {
      states[opt.value] = opt.textContent;
    });
    var shippingBiteshipDistrict = $("#_shipping_biteship_district");
    if (shippingBiteshipDistrict.autocomplete) {
      shippingBiteshipDistrict.autocomplete(getDistrictAutocompleteConfig($, states));
    }

    var shippingPostcode = document.getElementById("_shipping_postcode");
    if (phpVars.shouldUseDistricPostalCode && shippingPostcode !== null) {
      shippingPostcode = $("#_shipping_postcode");
      // shippingPostcode.autocomplete(getPostcodeAutocompleteConfig($))
    }

    $("#woocommerce-order-items").on("click", "button.add-biteship", addBiteship);
    $(document.body).on("wc_backbone_modal_loaded", backboneInit).on("wc_backbone_modal_response", backboneResponse);

    $("#order_biteship").click(function () {
      openOrderBiteshipModal(woocommerce_admin_meta_boxes.post_id);
    });

    $("#tracking-order").click(function () {
      openTrackingOrderModal(woocommerce_admin_meta_boxes.post_id);
    });

    $("#add-multiple-origin").click(function () {
      addModalMultiOrigin();
    });
    $("#close-modal-add-multi-origin").click(function () {
      closeModalMultiOrigin();
    });

    $("#mapx").click(function () {
      alert(1);
    });

    $("#data_biteship_order_id").ready(function () {
      var orderId = $("#data_biteship_order_id").text();
      if (!orderId) {
        return;
      }
      fetchOrder(
        orderId,
        function (data) {
          $("#delivery_status").text(data.status);
        },
        function (error) {
          $("#delivery_status").text(error);
        },
      );
    });

    $(".biteship_waybill").each(function (i, element) {
      var biteshipWaybillTd = $(element);
      var biteshipShopNameTd = biteshipWaybillTd.prev();
      var biteshipStatusTd = biteshipWaybillTd.next();
      var biteshipActionTd = biteshipStatusTd.next();
      // var orderStatusTd = biteshipWaybillTd.prev();
      var orderId = biteshipWaybillTd.parent().attr("id").split("-")[1]; // e.g. post-102 -> orderId is 102
      var biteshipOrderId = "";
      if (biteshipWaybillTd.children().length > 0) {
        var biteshipOrderIdSpan = $(biteshipWaybillTd.children()[0]);
        biteshipOrderId = biteshipOrderIdSpan.text();
      }

      var orderStatus = "";
      if (biteshipWaybillTd.children().length > 1) {
        var orderStatusSpan = $(biteshipWaybillTd.children()[1]);
        orderStatus = orderStatusSpan.text();
      }

      // displaying shop name
      if (parseInt(phpVars.multipleOriginsIsactive)) {
        getShopName(orderId, biteshipShopNameTd);
      }

      if (!biteshipOrderId) {
        biteshipActionTd.children().remove();
        biteshipActionTd.append(createCreateShipmentButton(orderId, orderStatus));
        return;
      }

      fetchOrder(
        biteshipOrderId,
        function (data) {
          biteshipActionTd.children().remove();
          if (data.courier && data.courier.waybill_id) {
            var waybillId = data.courier.waybill_id;
            var biteshipStatus = data.status;
            biteshipWaybillTd.append(`<mark class="order-status biteship-tips" data-tip="Biteship status: ${data.status}"><span>${waybillId}</span></mark>`);
            biteshipStatusTd.append(`<mark class="order-status"><span>${biteshipStatus}</span></mark>`);
            biteshipStatusTd.append(createShipmentTrackingButton(waybillId));

            var deleteButton = createDeleteShipmentButton(waybillId, orderId, biteshipStatus);
            biteshipActionTd.append(deleteButton);

            $(".biteship-tips").tipTip({
              attribute: "data-tip",
              fadeIn: 50,
              fadeOut: 50,
              delay: 200,
              keepAlive: true,
            });
          } else {
            biteshipWaybillTd.append(`<span>-</span>`);
          }
        },
        function (error) {
          biteshipActionTd.children().remove();
        },
      );
    });

    $("#doaction").click(function (event) {
      return onDoAction("#bulk-action-selector-top");
    });

    $("#doaction2").click(function (event) {
      return onDoAction("#bulk-action-selector-bottom");
    });

    $("#woocommerce_biteship_license").change(function () {
      var license = $("#woocommerce_biteship_license").val();
      if (license.length > 0) {
        const prefix = "biteship_wocm";
        let license_prefix = license.split(".")[0];
        if (prefix !== license_prefix) {
          $("#woocommerce_biteship_license").val("");
          alert(`Mohon Maaf, nampaknya Anda memasukkan kunci Biteship yang salah. Mohon gunakan kunci Biteship WooCommerce yang memiliki awalan 'biteship_wocm.'. Terima Kasih`);
          $("#woocommerce_biteship_license").css("border", "3px solid #FF0000");
        } else {
          $("#woocommerce_biteship_license").css("border", "3px solid #29cb35");
        }
      }
    });

    $("#woocommerce_biteship_gmap_api_key").change(function () {
      var gmapAPI = $("#woocommerce_biteship_gmap_api_key").val();
      if (gmapAPI.length > 0) {
        const addressTest = "Block 71, Ariobimo Sentral Tower".replace(/ /g, "%20");
        console.log(addressTest);
        $.ajax({
          url: `https://maps.googleapis.com/maps/api/geocode/json?address=${addressTest}&key=${gmapAPI}`,
          type: "GET",
          success: function (response) {
            switch (response.status) {
              case "OK":
                $("#woocommerce_biteship_gmap_api_key").css("border", "3px solid #29cb35");
                break;
              case "OVER_DAILY_LIMIT":
                alert(
                  'API Key is incorrect or The provided method of payment is no longer valid. See the <a href="https://developers.google.com/maps/faq#over-limit-key-error">Map FAQ</a> to learn how to fix this',
                );
                $("#woocommerce_biteship_gmap_api_key").css("border", "3px solid #FFFF00");
                break;
              case "OVER_QUERY_LIMIT":
                alert("Your quota has been exceeded");
                $("#woocommerce_biteship_gmap_api_key").css("border", "3px solid #FFFF00");
                break;
              case "ZERO_RESULTS":
                $("#woocommerce_biteship_gmap_api_key").css("border", "3px solid #FF0000");
                alert("The address could not be found. Please make sure you enter the real one.");
                break;
              case "REQUEST_DENIED":
                alert(
                  "Mohon maaf. Kunci API Google maps Anda belum memenuhi standard untuk digunakan pada plugin Biteship. Pastikan Anda aktifkan Javascript Maps, Geocode, dan Places API dari Google.",
                );
                $("#woocommerce_biteship_gmap_api_key").css("border", "3px solid #FF0000");
                break;
              case "INVALID_REQUEST":
                alert("The query (address, components or latitude/longitude) is missing.");
                $("#woocommerce_biteship_gmap_api_key").css("border", "3px solid #FF0000");
                break;
              default: //UNKNOWN_ERROR
                alert("The request could not be processed due to a server error. Please try again.");
                $("#woocommerce_biteship_gmap_api_key").css("border", "3px solid #FF0000");
                break;
            }
          },
        });
      }
    });

    $(document).ready(function () {
      if ($("div.error") !== undefined && getUrlVars()["biteship_error"] !== undefined) {
        alert(document.getElementsByClassName("error")[0].textContent);
      }
    });
  });

  function onDoAction(bulkActionSelector) {
    if ($(bulkActionSelector).val() === "create_biteship_shipment") {
      openOrderBiteshipModal(null);
      return false;
    }
    return true;
  }

  function getShopName(orderId, biteshipShopNameTd) {
    var data = {
      action: "biteship_admin_shop_information",
      orderId: orderId,
      security: phpVars.orderBiteshipNonce,
      dataType: "json",
    };
    $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
      if (response.success) {
        biteshipShopNameTd.append(`<strong>${response.data.shop_name.length > 0 ? response.data.shop_name : "-"}</strong>`);
      } else {
        console.log("ERROR!!!");
      }
    });
  }

  function createCreateShipmentButton(orderId, status) {
    var statusListWithCreateButtonShown = ["pending-payment", "processing", "on-hold"];
    if (!statusListWithCreateButtonShown.includes(status)) {
      return;
    }

    var disabled = status === "processing" ? "" : "disabled";
    var createButton = $(`<a class="button" id="createButton${orderId}" ${disabled}>Create Shipment</a>`);
    createButton.click(function (event) {
      var disabled = $(this).attr("disabled");
      if (disabled) {
        return;
      }

      event.stopPropagation();
      openOrderBiteshipModal(orderId);
    });
    return createButton;
  }

  function createShipmentTrackingButton(waybill_id) {
    if (phpVars.trackingPageUrl.length > 0 && phpVars.trackingPageIsactive) {
      var createButton = $(`<a class="button" target="_blank" rel="noopener noreferrer" href="${phpVars.trackingPageUrl}&waybill_id=${waybill_id}" style="margin-top: 10px;">Lacak</a>`);
      createButton.click(function (event) {
        var disabled = $(this).attr("disabled");
        if (disabled) {
          return;
        }

        event.stopPropagation();
        openOrderBiteshipModal(orderId);
      });
      return createButton;
    }
  }

  function createDeleteShipmentButton(waybillId, orderId, biteshipStatus) {
    var nonCancelableOrderStatus = ["cancelled", "picked", "dropping_off", "delivered", "cancelled", "rejected", "courier_not_found", "disposed", "returned"];
    var disabled = "";
    var tips = "";
    var dataTip = "";
    if (nonCancelableOrderStatus.includes(biteshipStatus)) {
      disabled = "disabled";
      tips = "biteship-tips";
      dataTip = biteshipStatus === "cancelled" ? 'data-tip="Sudah dibatalkan"' : `data-tip="Dalam pengiriman"`;
    }

    var deleteButton = $(`<a class="button ${disabled} ${tips} biteship-cancel-shipment-button" ${dataTip}>Cancel Shipment</a>`);
    deleteButton.click(function (event) {
      var disabled = $(this).attr("disabled");
      if (disabled) {
        return;
      }

      $(this).attr("disabled", "disabled").text("Cancelling...");

      event.stopPropagation();
      if (confirm(`Cancelling shipment with waybill: ${waybillId}, are you sure?`)) {
        deleteBiteshipShipping(
          orderId,
          function () {
            location.reload();
          },
          function (error) {
            alert(error);
          },
        );
      }
    });
    return deleteButton;
  }

  function addBiteship() {
    $(this).WCBackboneModal({
      template: "biteship-modal-add-shipping",
    });

    return false;
  }

  function addModalMultiOrigin() {
    $("#add-multi-origin").show();
  }

  function closeModalMultiOrigin() {
    $("#add-multi-origin").hide();
  }

  function openOrderBiteshipModal(orderId) {
    $(document).WCBackboneModal({
      template: "biteship-modal-order-biteship",
      variable: {
        order_id: orderId,
        is_bulk: orderId == null,
        shipper_name: phpVars.biteshipShipperName,
        shipper_phone_no: phpVars.biteshipShipperPhoneNo,
      },
    });

    return false;
  }

  function openTrackingOrderModal(orderId) {
    var data = {
      action: "biteship_admin_get_order_trackings",
      orderId: orderId,
      security: phpVars.orderBiteshipNonce,
      dataType: "json",
    };
    $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
      if (response.success) {
        let itemsHtml = "";
        for (var i = 0; i < response.data.items.length; i++) {
          itemsHtml += `<tr> <td>${response.data.items[i].name}</td><td><div class=""><div>${response.data.items[i].quantity}</div></div></td></tr>`;
        }
        $(document).WCBackboneModal({
          template: "biteship-modal-tracking-biteship",
          variable: {
            items: itemsHtml,
            order_id: orderId,
            waybill_id: response.data.waybill_id,
            waybill_url: phpVars.trackingPageUrl,
          },
        });
      } else {
        window.alert("ERROR!!!");
      }
    });
  }

  function backboneInit(event, target) {
    if (target === "biteship-modal-add-shipping") {
      fetchShippingList();
    }
  }

  function backboneResponse(event, target, backboneData) {
    if (target === "biteship-modal-add-shipping") {
      addShipping(backboneData);
    } else if (target === "biteship-modal-order-biteship") {
      if (backboneData.is_bulk === "true") {
        orderBiteshipShippingBulk(backboneData);
      } else {
        orderBiteshipShipping(backboneData);
      }
    }
  }

  function blockOrderItem() {
    $("#woocommerce-order-items").block({
      message: null,
      overlayCSS: {
        background: "#fff",
        opacity: 0.6,
      },
    });
  }

  function unblockOrderItem() {
    $("#woocommerce-order-items").unblock();
  }

  function deleteBiteshipShipping(orderId, onSuccess, onError) {
    var data = {
      action: "biteship_admin_delete_order_biteship",
      orderId: orderId,
      security: phpVars.orderBiteshipNonce,
      dataType: "json",
    };

    $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
      if (!response.success) {
        onError(response.data.error);
        return;
      }
      onSuccess();
    });
  }

  function orderBiteshipShippingBulk(backboneData) {
    var inputSenderName = $("<input>").attr("type", "hidden").attr("name", "sender_name").val(backboneData.sender_name);
    var inputSenderPhoneNo = $("<input>").attr("type", "hidden").attr("name", "sender_phone_no").val(backboneData.sender_phone_no);
    var inputDeliveryTimeOption = $("<input>").attr("type", "hidden").attr("name", "delivery_time_option").val(backboneData.time_option);

    var momentDatetime = moment();
    if (backboneData.time_option == "later") {
      momentDatetime = moment(backboneData.time, "DD-MM-YYYY HH:mm");
    }
    var inputDeliveryDate = $("<input>").attr("type", "hidden").attr("name", "delivery_date").val(momentDatetime.format("YYYY-MM-DD"));
    var inputDeliveryTime = $("<input>").attr("type", "hidden").attr("name", "delivery_time").val(momentDatetime.format("HH:mm"));

    var checkboxes = $('input[id^="cb-select-"]');
    for (var i = 0; i < checkboxes.length; i++) {
      const checkbox = $(checkboxes[i]);
      if (checkbox.is(":checked")) {
        disableCreateButtonOnCreating(checkbox.val());
      }
    }

    $("#posts-filter").append(inputSenderName);
    $("#posts-filter").append(inputSenderPhoneNo);
    $("#posts-filter").append(inputDeliveryTimeOption);
    $("#posts-filter").append(inputDeliveryDate);
    $("#posts-filter").append(inputDeliveryTime);
    $("#posts-filter").submit();
  }

  function orderBiteshipShipping(backboneData) {
    blockOrderItem();
    var orderId = backboneData.order_id;
    var senderName = backboneData.sender_name;
    var senderPhoneNo = backboneData.sender_phone_no;

    disableCreateButtonOnCreating(orderId);
    var momentDatetime = moment();
    var data = {
      action: "biteship_admin_order_biteship",
      orderId: orderId,
      senderName: senderName,
      senderPhoneNo: senderPhoneNo,
      deliveryDate: momentDatetime.format("YYYY-MM-DD"),
      deliveryTime: momentDatetime.format("HH:mm"),
      deliveryTimeOption: "now",
      security: phpVars.orderBiteshipNonce,
      dataType: "json",
    };

    $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
      unblockOrderItem();
      if (response.success) {
        location.reload();
      } else {
        window.alert(response.data.error);
        location.reload();
      }
    });
  }

  function disableCreateButtonOnCreating(orderId) {
    var createButton = $(`#createButton${orderId}`);
    if (createButton) {
      createButton.attr("disabled", "disabled").text("Creating...");
    }
  }

  function addShipping(backboneData) {
    blockOrderItem();
    var rateArr = backboneData.shipping_method.split("||");
    if (rateArr.length != 4) {
      return;
    }
    var title = rateArr[0];
    var rate = parseFloat(rateArr[1]);
    var courierCode = rateArr[2];
    var courierServiceCode = rateArr[3];

    var data = {
      action: "biteship_admin_add_biteship_order_shipping",
      orderId: woocommerce_admin_meta_boxes.post_id,
      methodTitle: title,
      rate: rate,
      courierCode: courierCode,
      courierServiceCode: courierServiceCode,
      security: woocommerce_admin_meta_boxes.order_item_nonce,
      dataType: "json",
    };

    $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
      if (response.success) {
        $("table.woocommerce_order_items tbody#order_shipping_line_items").append(response.data.html);
      } else {
        window.alert(response.data.error);
      }
      unblockOrderItem();
    });

    return false;
  }

  //Ilyasa - Edited Fetch Order
  function fetchOrder(orderId, onFetched, onError) {
    $.ajax({
      url: `${phpVars.biteshipBaseUrl}/v1/woocommerce/orders/${orderId}`,
      headers: { Authorization: phpVars.biteshipLicenseKey },
      success: function (response) {
        if (response.success) {
          onFetched(response);
        } else {
          onError(response.error);
        }
      },
      error: function (error) {
        if (error.responseJSON) {
          onError(error.responseJSON.error);
          return;
        }
        onError("failed retrieving status from biteship");
      },
    });

    // Get Status Update from Meta Data Order Woocommerce
    // var data = {
    //   action: 'biteship_admin_get_biteship_order_status',
    //   security: phpVars.orderBiteshipNonce,
    //   biteship_order_id: orderId,
    //   dataType: 'json'
    // };

    // $.post(woocommerce_admin_meta_boxes.ajax_url, data, function(response) {
    //   if(response.success) {
    //     onFetched(response.data);
    //   } else {
    //     onError(response.error);
    //   }
    // });
  }

  function fetchShippingList() {
    var shippingListLoading = $("#shippingListLoading");
    shippingListLoading.text("Memuat pengiriman...");

    var destinationCoordinate = "";
    var destinationLatitude = "";
    var destinationLongitude = "";
    if ($("#_shipping_biteship_location_coordinate").val()) {
      destinationCoordinate = $("#_shipping_biteship_location_coordinate").val().split(",");
      if (destinationCoordinate.length === 2) {
        destinationLatitude = destinationCoordinate[0];
        destinationLongitude = destinationCoordinate[1];
      }
    }
    var data = {
      action: "biteship_admin_fetch_shipping_rates",
      security: phpVars.getShippingRatesNonce,
      orderId: woocommerce_admin_meta_boxes.post_id,
      destinationZipcode: $("#_shipping_postcode").val(),
      destinationLatitude: destinationLatitude,
      destinationLongitude: destinationLongitude,
      dataType: "json",
    };

    $.post(woocommerce_admin_meta_boxes.ajax_url, data, function (response) {
      if (response.success) {
        if (response.data.rates) {
          for (var i = 0; i < response.data.rates.length; i++) {
            const rate = response.data.rates[i];
            const radio = $(
              `<li><input type="radio" name="shipping_method" id="${rate.id}" value="${rate.label}||${rate.cost}||${rate.meta_data.courier_code}||${rate.meta_data.courier_service_code}"/><label for="${rate.id}">${rate.label}: Rp${addCommas(rate.cost, 0, ".", ",")}</label></li>`,
            );
            radio.appendTo("#shippingList");
          }
        }
      } else {
        alert("Failed fetching shipping rates");
      }
      shippingListLoading.text("");
    });
  }

  function setPosition(marker, map, location) {
    marker.setPosition(location);
    marker.setMap(map);
    if ($("#position-input").length) {
      $("#position-input").val(location.lat() + "," + location.lng());
      return;
    }
  }
})(jQuery);

function getDistrictAutocompleteConfig(jquery, states) {
  return {
    source: function (request, response) {
      var baseURL = phpVars.biteshipBaseUrl || "";
      var query = request.term.replace(/ /g, "+");
      jquery.ajax(`${baseURL}/v1/maps/areas?countries=ID&input=${query}&type=single`, {
        headers: {
          authorization: `Bearer ${phpVars.biteshipLicenseKey || ""}`,
        },
        success: function (data) {
          if (!data.success) {
            response([]);
          }
          var result = data.areas.map(function (val) {
            var itemStr = `${val.administrative_division_level_3_name}, ${val.administrative_division_level_2_name}, ${val.administrative_division_level_1_name}`;
            return {
              label: itemStr,
              value: val.administrative_division_level_3_name,
              district: val.administrative_division_level_3_name,
              city: val.administrative_division_level_2_name,
              state: val.administrative_division_level_1_name,
              id: val.id,
            };
          });
          response(result);
        },
        error: function (error) {
          alert("Could not search district");
        },
      });
    },
    select: function (event, ui) {
      if (ui.item) {
        jquery(`#_shipping_city`).val(ui.item.city);
        var stateKey = getKeyByValue(states, ui.item.state);
        jquery(`#_shipping_state`).val(stateKey);
        jquery(`#_shipping_state`).select2().trigger("change");
        jquery(`#_shipping_postcode`).autocomplete("search", ui.item.id);
      }
    },
  };
}

function getPostcodeAutocompleteConfig(jquery) {
  return {
    source: function (request, response) {
      var baseURL = phpVars.biteshipBaseUrl || "";
      jquery.ajax(`${baseURL}/v1/maps/areas/${request.term}`, {
        headers: {
          authorization: `Bearer ${phpVars.biteshipLicenseKey || ""}`,
        },
        success: function (data) {
          if (!data.success) {
            response([]);
          }
          var result = data.areas.map(function (val) {
            return `${val.postal_code}`;
          });
          response(result);
        },
        error: function (error) {
          alert("Could not search postcode");
        },
      });
    },
    select: function (event, ui) {},
  };
}

function getKeyByValue(object, value) {
  return Object.keys(object).find((key) => object[key].toUpperCase() === value.toUpperCase());
}

function addCommas(nStr) {
  nStr += "";
  x = nStr.split(",");
  x1 = x[0];
  x2 = x.length > 1 ? "," + x[1] : "";
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, "$1" + "." + "$2");
  }
  return x1 + x2;
}

function getUrlVars() {
  var vars = [],
    hash;
  var hashes = window.location.href.slice(window.location.href.indexOf("?") + 1).split("&");
  for (var i = 0; i < hashes.length; i++) {
    hash = hashes[i].split("=");
    vars.push(hash[0]);
    vars[hash[0]] = hash[1];
  }
  return vars;
}

function hideComponent($) {
  /* this only for order page */
  setTimeout(function () {
    $(`._shipping_biteship_province_field`).hide();
    $(`._shipping_biteship_city_field`).hide();
    $(`._shipping_biteship_district_field`).hide();
    $(`._shipping_biteship_zipcode_field`).hide();
  }, 1800);
}

function createComponent($) {
  //ilyasa bugfix saldo pengiriman dont show before just window.location.toString().includes('shop_order')
  if (window.location.toString().includes("wc-orders") || window.location.toString().includes("shop_order")) {
    if (!phpVars) {
      phpVars = {};
    }
    const wordingBitepoint = "Saldo deposito yang digunakan untuk membayar ongkos kirim. Abaikan jika kamu menggunakan sistem invoice/credit";
    const template = `
    <br>
      <div style="margin: 11px 2px 11px;">
        <pan>Saldo Pengiriman : </span> <input type="text" value="${phpVars.bitesPoint}" style="width: 106px;" disabled> <span class="woocommerce-help-tip" data-tip="${wordingBitepoint}"></span>
      </div>
      <div class="woocommerce-store-alerts__actions">
          <a href="http://bit.ly/3Us7pYe" class="components-button is-secondary" style="width: 92px;height: 30px;" target="_blank">Tambah Saldo</a>
      </div>
    `;
    $("ul.subsubsub").append(template);
  }
}
