<?php

$basePadding = isset($chosenTemplate->base_padding_uol) ? $chosenTemplate->base_padding_uol : 2.11;
$baseUol = isset($chosenTemplate->uol) ? $chosenTemplate->uol : "mm";

return "<div class=\"template-container\"></div>
<style type=\"text/css\">
    * { -webkit-box-sizing: border-box; box-sizing: border-box; }
    .custom-template { text-align: left; line-height: 1; }
    body { padding: {$basePadding}{$baseUol}; margin: 0; font-family: sans-serif; background-color: white; font-family: sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; font-size: 20px; }
    body * { line-height: 1; }
</style>";
