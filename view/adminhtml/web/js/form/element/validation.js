define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function (Abstract, $, validator) {
    'use strict';

    validator.addRule(
        'validate-length-of-numbers-after-comma',
        function (value) {
            return /^\d+(\.\d{0,2})?$/.test(value);
        },
        $.mage.__('The field should contain no more than 2 decimal places.')
    );

    return Abstract.extend({
        'validate-length-of-numbers-after-comma': function () {
            return validator('validate-length-of-numbers-after-comma', this.value()).passed;
        }
    });
});