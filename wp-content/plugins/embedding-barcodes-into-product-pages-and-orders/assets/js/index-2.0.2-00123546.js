var BarcodeDigitalAdminMenuList = function () {
  try {
    let wpVersion = window.a4bjsDigital.wp_version;
    jQuery("#adminmenu span.a4barcodeDigital_support")
      .closest("a")
      .attr("target", "_blank")
      .attr("href", "https://www.ukrsolution.com/ExtensionsSupport/Support?extension=23&version=2.0.2&pversion=" + wpVersion + "&d=" + btoa(window.a4barcodesGSDigital.lk));
    jQuery("#adminmenu span.a4barcodeDigital_faq")
      .closest("a")
      .attr("target", "_blank")
      .attr("href", "https://www.ukrsolution.com/Joomla/A4-BarCode-Generator-For-Wordpress#faq");
  } catch (error) {
    console.error(error.message);
  }
};


var BarcodeLoaderMethods = function (params) {
  let prefix = params && params.prefix ? params.prefix : "";

  let a4bPreloader = function (status) {
    jQuery("#a4b-preloader-scripts").remove();

    if (status) {
      let css = `
      #a4b-preloader-scripts {position: fixed;top: 0px;left: 0px;width: 100vw;height: 100vh;z-index: 9000;font-size: 14px;background: rgba(0, 0, 0, 0.3);transition: opacity 0.3s ease 0s;transform: translate3d(0px, 0px, 0px);    }
      #a4b-preloader-scripts .a4b-preloader-icon {position: relative;top: 50%;left: 50%;color: #fff;border-radius: 50%;opacity: 1;width: 30px;height: 30px;border: 2px solid #f3f3f3;border-top: 3px solid #3498db;display: inline-block;animation: a4b-spin 1s linear infinite;    }
      @keyframes a4b-spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
      `;
      let preloader = jQuery(`<div id="a4b-preloader-scripts"><span class="a4b-preloader-icon"></span></div>`);

      jQuery("#wpbody-content").append(`<style>${css}</style>`);
      jQuery("#wpbody-content").append(preloader);
    }
  };

  let a4bGetScriptByPath = function (path) {
    return jQuery.ajax({
      type: "GET",
      url: path,
      success: function () { },
      dataType: "script",
      cache: false,
    });
  };

  let a4bLoadScript = function (el, pluginData) {
    a4bPreloader(true);
    if (pluginData.vendorJsPath !== "") {
      var a = a4bGetScriptByPath(pluginData.appJsPath);
      var v = a4bGetScriptByPath(pluginData.vendorJsPath);
      var jszip = a4bGetScriptByPath(pluginData.jszip);

      return jQuery.when(jszip, a, v).done(function () {
        a4bPreloader(false);
        el.click();
        window.BarcodesDigitalAppStatus = true;

        return true;
      });
    } else {
      var jszip = a4bGetScriptByPath(pluginData.jszip);
      jQuery.getCachedScript(jszip);

      return jQuery.getCachedScript(pluginData.appJsPath).done(function () {
        a4bPreloader(false);
        el.click();
        window.BarcodesDigitalAppStatus = true;

        return true;
      });
    }
  };

  return { a4bLoadScript };
};

var BarcodeDigitalLoader = new BarcodeLoaderMethods({ prefix: "d_" });

jQuery.getCachedScript = function (url, options) {
  options = jQuery.extend(options || {}, {
    dataType: "script",
    cache: true,
    crossDomain: true,
    url: url,
  });

  return jQuery.ajax(options);
};

jQuery(document).ready(function () {
  let prefix = "";
  BarcodeDigitalAdminMenuList();

  prefix = "_d";
  let s = 'a[id="barcode' + prefix + '-shortcodes-section"]';
  s += ',a[id="barcode' + prefix + '-settings-section"]';
  s += ',a[id="barcode' + prefix + '-custom-templates"]';


  let menu = jQuery(s);
  menu.off("click");

  let startLoading = function (e) {

    e.preventDefault();
    e.stopPropagation();

    let action = jQuery(this).attr("data-action-type");
    jQuery("body").attr("data-barcodes-action", action);

    menu.off("click");
    menu.click(function (e) {
      e.preventDefault();
      e.stopPropagation();

      let itemId = jQuery(this).attr("data-item-id");
      if (itemId) window.barcodeSingleItemId = itemId;
      else window.barcodeSingleItemId = undefined;
    });


    BarcodeDigitalLoader.a4bLoadScript(jQuery(this), a4bjsDigital);

    return false;
  };

  menu.click(startLoading);


  let shortcodes = jQuery('a[id="barcode' + prefix + '-shortcodes-section"]');
  if (shortcodes.length > 0) {
    shortcodes.click();
  }

  let settings = jQuery('a[id="barcode' + prefix + '-settings-section"]');
  if (settings.length > 0) {
    settings.click();
  }

  let templates = jQuery('a[id="barcode' + prefix + '-custom-templates"]');
  if (templates.length > 0) {
    templates.click();
  }
});

