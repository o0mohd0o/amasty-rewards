/*jshint browser:true jquery:true*/
define([
    "jquery",
    "Magento_Ui/js/modal/modal"
], function($){
    "use strict";

    $.widget('mage.amrewardPoints', {
        options: {
        },
        _create: function () {
            this.rewardAmount = $(this.options.rewardAmount);

            this.removeReward = $(this.options.removeRewardSelector);

            $(this.options.applyButton).on('click', $.proxy(function () {
                this.rewardAmount.attr('data-validate', '{required:true}');

                this.removeReward.attr('value', '0');
                $(this.element).validation().submit();
            }, this));

            $(this.options.cancelButton).on('click', $.proxy(function () {
                this.rewardAmount.removeAttr('data-validate');
                this.removeReward.attr('value', '1');
                this.element.submit();
            }, this));

            if (!this.isGreaterThanMinimumBalance()) {
                this.getMinimumRewardNoteDOM().show();
                this.disableRewardInput();

            }
        },

        /**
         *
         * @returns {boolean}
         */
        isGreaterThanMinimumBalance: function () {
            var realBalance = this.options.customerBalance;

            if (this.options.usedPoints) {
                realBalance += this.options.usedPoints;
            }

            return !this.options.minimumBalance || (realBalance >= this.options.minimumBalance);
        },

        /**
         * @return void
         */
        disableRewardInput: function() {
            $(this.options.applyButton).prop("disabled", true);
            $(this.options.rewardAmount).prop("disabled", true);
        },

        /**
         *
         * @returns {*|jQuery|HTMLElement}
         */
        getMinimumRewardNoteDOM: function() {
            return $(this.options.minimumNoteSelector);
        }
    });

    return $.mage.amrewardPoints;
});
