<?php
?>

<style>
    .barcodes-show-popup {
        float: none !important;
        margin: 4px 5px 0 !important;
        font-weight: 400;
    }

    .barcodes-show-popup img {
        position: relative;
        top: 4px;
        height: 19px;
    }
</style>
<button type="button" class="button barcodes-show-popup" title="Product Barcode" onclick="barcodeShowPopup('<?php echo $jsShortcode; ?>')">
    <!-- <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAATCAYAAACDW21BAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5QkcDRIprxzRKwAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAAE4SURBVEjH7c0xyzkBAMfxb89zSTG4/YbbpFBcNoOFyWjxCiwmK8VmMN5LUCyidAspJbFdMphNEusp6fT7v4t/z3CfN/Dh/X6r2WxqMpno+/2q3W6r2+0qDEM1Gg31+33t93uNx2NJ0vl8VqvVUhAEej6fGg6HGo1G6vV6kiTXdeX7viSpXq/LdV0tFgt1Oh09Hg/9hGHIdDrldDohieVyyWq1QhKz2YzNZsP1esX3fQDu9zvz+ZzP58Pr9WK73bLb7Viv1wAcj0dutxsAnudxOBy4XC54nkcQBPzwn0VhFEZhFEZhFP7B8HcwGAxSqRSVSgXbtkkmk5TLZfL5PIlEgmq1SqFQwLIsbNvGMAwsy6JUKhGLxTBNE8dxcByHXC5HPB4nk8lgmiaGYVCr1chms6TTaYrFIv8AJimfJYA0+9UAAAAASUVORK5CYII=" alt="" /> -->
    <span class="dashicons-before dashicons-tag"></span>
</button>

<?php
?>