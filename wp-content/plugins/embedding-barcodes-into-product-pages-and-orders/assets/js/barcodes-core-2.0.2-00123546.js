jQuery(document).ready(function (e) {
  jQuery("input.variation_id[name='variation_id']").change(function () {
    let el = jQuery(this).parent().find("input[name='product_id']");
    let productId = el ? el.val() : "";
    let variationId = jQuery(this).val();
    if (variationId) digitalBarcodeEmbedded().updateBarcode({ productId: variationId, type: "variation" });
    else if (productId) digitalBarcodeEmbedded().updateBarcode({ productId, type: "simple" });
  });
  if (jQuery("input.variation_id[name='variation_id']").length) jQuery("input.variation_id[name='variation_id']").change();
});

var digitalBarcodeEmbeddedRequest = null;
var digitalBarcodeEmbedded = function () {
  let updateBarcode = function (params) {
    digitalBarcodeEmbeddedRequest = jQuery.ajax({
      type: "POST",
      url: window.digitalBarcodeJS.ajaxUrl,
      data: { action: window.digitalBarcodeJS.routes.generateBarcode, ...params },
      beforeSend: () => {
        if (digitalBarcodeEmbeddedRequest !== null) digitalBarcodeEmbeddedRequest.abort();
      },
      success: (result) => {
        if (result && result.image) jQuery("img.digital-barcode-embedded").replaceWith(result.image);
        digitalBarcodeEmbeddedRequest = null;
      },
      dataType: "JSON",
    });
  };

  return { updateBarcode };
};
