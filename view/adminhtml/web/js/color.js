define([
    "jquery",
    "jquery/colorpicker/js/colorpicker"
], function ($) {
    'use strict';

    return function (config) {
        $(document).ready(function () {
            var input = $("#" + config.htmlId);

            function inverse(color) {
                return (0xFFFFFF - ("0x" + color)).toString(16).padStart(6, "0").toUpperCase();
            }

            if (config.inverseHex !== '') {
                input.css({"backgroundColor" : config.value, "color": config.inverseHex});
            }

            input.ColorPicker({
                color: "' . $value . '",
                onChange: function (hsb, hex) {
                    input.css({"backgroundColor": "#" + hex, "color": "#" + inverse(hex)}).val("#" + hex);
                }
            });
        });
    };
});
