define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    var MAX_VALUES_LIMIT = 10;

    var validate = function (value) {
        if (value.split(';').length > 1 || value.match(/^\s*(\w\s*){2,}$/)) {
            return $t('Use a comma-separated list.');
        }

        var days = value.split(',');

        if (days.length > MAX_VALUES_LIMIT) {
            return $t('Limited to %1 N values.').replace('%1', MAX_VALUES_LIMIT);
        }

        var invalidNumberMessage = $t('Please enter a number greater than 0 in this field.');
        for (var i = 0; i < days.length; i++) {
            var day = days[i].trim();

            if (!day.match(/^\d+$/)) {
                return invalidNumberMessage;
            }

            day = parseInt(day);
            if (isNaN(day)) {
                return invalidNumberMessage;
            }

            if (day < 1) {
                return invalidNumberMessage;
            }
        }

        return null;
    };

    return function () {
        $.validator.addMethod(
            'validate-notification-days',
            function (value) {
                return validate(value) === null;
            },
            function (value, element) {
                return validate($(element).val());
            }
        )
    }
});
