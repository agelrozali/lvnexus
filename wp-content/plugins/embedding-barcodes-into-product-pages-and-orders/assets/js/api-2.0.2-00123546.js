window.BarcodeDigitalAppStorage = new (class BarcodeStorage {
  async zipStorage(barcodeList, storageKey) {
    var zip = new USJSZip();
    zip.file("labels.txt", JSON.stringify(barcodeList));

    return zip.generateAsync({ type: "binarystring", compression: "DEFLATE", compressionOptions: { level: 5 } }).then((content) => {
      window.localStorage.setItem(storageKey, content);
      return true;
    });
  }

  async unzipStorage(storageKey) {
    const binarystring = window.localStorage.getItem(storageKey);

    if (!binarystring) return [];
    var unZip = new USJSZip();

    return unZip.loadAsync(binarystring).then((zip) => {
      return zip
        .file("labels.txt")
        .async("string")
        .then((content) => {
          return JSON.parse(content);
        });
    });
  }

  async addLabel(image, format, replacements) {
    let label = {
      format,
      image,
      line1: typeof replacements === "object" && replacements["[line1]"] ? replacements["[line1]"] : "",
      line2: typeof replacements === "object" && replacements["[line2]"] ? replacements["[line2]"] : "",
      line3: typeof replacements === "object" && replacements["[line3]"] ? replacements["[line3]"] : "",
      line4: typeof replacements === "object" && replacements["[line4]"] ? replacements["[line4]"] : "",
      post_image: "",
      replacements: typeof replacements === "object" ? replacements : [],
    };

    let storageKey = "barcodes-list-demo";

    return this.unzipStorage(storageKey).then((list) => {
      let index = list.findIndex((item) => !item.image);

      if (index >= 0) list[index] = label;
      else list.push(label);

      return this.zipStorage(list, storageKey).then(() => {
        if (window.BarcodesDigitalAppStatus) window.BarcodesDigitalApp.reloadBarcodesList();
    });
  });
}
}) ();

var BarcodesDigitalMessage = 'JavaScript API available only for Premium plan: https://www.ukrsolution.com/Wordpress/Print-Barcode-Labels-for-WooCommerce-Products#plans';
var BarcodesDigital = new (class BarcodesDigital {
  async addLabel(labelData = { barcodeImageData: "", format: "C128", replacements: [] }) { console.warn(BarcodesDigitalMessage); }

  show() { console.warn(BarcodesDigitalMessage); }
})();
