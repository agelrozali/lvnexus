jQuery(document).ready(function (e) {
  jQuery("iframe.barcode-pereview-item").each(function (i) {
    barcodeEmbedingInitContent(jQuery(this));

    jQuery(this).on("load", function () {
      barcodeEmbedingInitContent(jQuery(this));
    });
  });
});

function barcodeEmbedingInitContent(iframe) {
  let template = iframe.attr("data-template");
  let templateWrapHtml = iframe.attr("data-template-wrapper");

  if (!template && !templateWrapHtml) return;

  let templateBody = iframe.contents().find("body");
  templateBody.html(templateWrapHtml);

  let templateWrapper = iframe.contents().find(".template-container");
  templateWrapper.html(template);

  if (!jQuery("body").hasClass("wp-admin")) {
    iframe
      .removeAttr("data-template")
      .removeAttr("data-template-wrapper")
      .removeClass("barcode-pereview-item");
  }

  checkIframeTemplate(iframe);
}

function checkIframeTemplate(iframe) {
  const iframeContent = iframe.contents().find("body");
  const productId = iframe.data("pid");

  let labelData = {
    lineBarcode: "190198457325",
    line1: "190198457325",
    line2: "Apple iPhone X 64Gb",
    line3: "799.99 $",
    line4: "Computers & Electronics",
  };

  if (typeof barcodeItemsData === "object" && barcodeItemsData[productId]) {
    labelData = {
      lineBarcode: barcodeItemsData[productId].lineBarcode,
      line1: barcodeItemsData[productId].line1,
      line2: barcodeItemsData[productId].line2,
      line3: barcodeItemsData[productId].line3,
      line4: barcodeItemsData[productId].line4,
    };
  }

  if (iframeContent.length > 0) setupAdaptiveTemplate({ element: iframeContent[0], data: labelData, });

}

const setupAdaptiveTemplate = ({ element }) => {
  if (!element) return;
  const template = element.querySelector("div[adaptive]");

  if (!template) return;

  const lines = template.querySelectorAll("div>div");

  if (lines.length !== 6) return;

  const labelWidth = template.clientWidth;
  const labelHeight = template.clientHeight;

  const imgLineHeight = labelHeight / 3;
  const lineHeight = Math.floor(imgLineHeight / 2);
  const fontSize = lineHeight * 0.85;

  lines.forEach((line) => {
    line.style.overflow = `hidden`;
    line.style.width = `${labelWidth}px`;
    line.style.fontSize = `${fontSize}px`;
    line.style.lineHeight = `${lineHeight}px`;
    line.style.verticalAlign = "middle";
    line.style.lineBreak = "anywhere";
  });

  lines[1].style.maxHeight = `${lineHeight}px`;

  lines[2].style.maxHeight = `${lineHeight}px`;

  lines[3].style.height = `${imgLineHeight}px`;
  const img = lines[3].querySelector("img");
  if (img) {
    img.style.objectFit = "cover";
    img.style.height = `${imgLineHeight}px`;
  }

  lines[4].style.maxHeight = `${lineHeight}px`;

  lines[5].style.maxHeight = `${lineHeight}px`;
};
