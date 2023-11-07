require([
    'jquery',
    'mage/translate',
    'jquery/validate'
    ],
    function ($) {
        $.validator.addMethod(
            'validate-length-of-numbers-after-comma',
            function (v) {
                return /^\d+(\.\d{0,2})?$/.test(v);
            },
            $.mage.__('The field should contain no more than 2 decimal places.')
        );
    }
);
