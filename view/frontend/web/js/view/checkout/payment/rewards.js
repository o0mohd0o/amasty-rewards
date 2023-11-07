define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Rewards/js/action/add-reward',
    'Amasty_Rewards/js/action/cancel-reward',
    'Amasty_Rewards/vendor/tooltipster/js/tooltipster.min',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Customer/js/model/customer'
], function (
    $,
    _,
    Component,
    quote,
    setRewardPointAction,
    cancelRewardPointAction,
    tooltipster,
    messageList,
    $t,
    customer,
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Rewards/checkout/payment/rewards',
            isApplied: false,
            pointsUsed: 0,
            pointsLeft: 0,
            noticeMessage: '',
            minimumPointsValue: 0,
            disableElem: false,
            isRewardsTooltipEnabled: false,
            rewardsTooltipContent: '',
            selectors: {
                tooltipElement: '[data-amrewards-js="tooltip"]'
            }
        },

        initObservable: function () {
            this._super();
            this.observe(['pointsUsed', 'pointsLeft', 'isApplied', 'noticeMessage', 'disableElem']);

            return this;
        },

        /**
         * @return {exports}
         */
        initialize: function () {
            this.setCustomerNull();
            this._super();
            this.isApplied(false);

            if (this.pointsUsed() > 0) {
                this.isApplied(true);
            }

            if (_.isUndefined(Number.parseFloat)) {
                Number.parseFloat = parseFloat;
            }

            if (this.getMinimumPointsValue() > this.pointsLeft() + Number.parseFloat(this.pointsUsed())) {
                this.disableElem(true);
            }

            this.initTooltip();
            this.setCustomerNull();

            return this;
        },

        setCustomerNull: function () {
            if (!customer.isLoggedIn()) {
                this.customerId = null;
            }
        },

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.customerId;
        },

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setRewardPointAction(this.pointsUsed, this.isApplied, this.pointsLeft, this.rateForCurrency, this.noticeMessage);
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            cancelRewardPointAction(this.isApplied);
            this.pointsLeft((Number.parseFloat(this.pointsLeft()) + Number.parseFloat(this.pointsUsed())).toFixed(2));
        },

        /**
         *
         * @return {*}
         */
        getRewardsCount: function () {
            return this.pointsLeft();
        },

        /**
         *
         * @return {*}
         */
        getPointsRate: function () {
            return this.pointsRate;
        },

        /**
         *
         * @return {*}
         */
        getCurrentCurrency: function () {
            return this.currentCurrencyCode;
        },

        /**
         *
         * @return {*}
         */
        getRateForCurrency: function () {
            return this.rateForCurrency;
        },

        /**
         * @return {*}
         */
        getMinimumPointsValue: function () {
            return Number.parseFloat(this.minimumPointsValue);
        },

        /**
         * @return {Boolean}
         */
        canApply: function () {
            return !(this.disableElem() || this.isApplied());
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-reward-form',
                valueValid = (this.pointsLeft() - this.pointsUsed() >= 0) && this.pointsUsed() > 0;

            if ($(form).validation() && $(form).validation('isValid') && valueValid) {
                return true;
            }

            messageList.addErrorMessage({
                message: $t('Points "' + this.pointsUsed() +'" not valid.')
            });

            return false;
        },

        initTooltip: function () {
            var tooltipTrigger = this.isTouchDevice() ? 'click' : 'hover';

            if (!this.isRewardsTooltipEnabled) {
                return;
            }

            $.async(this.selectors.tooltipElement, function () {
                $(this.selectors.tooltipElement).tooltipster({
                    position: 'right',
                    contentAsHtml: true,
                    interactive: true,
                    trigger: tooltipTrigger
                });
            }.bind(this));
        },

        isTouchDevice: function () {
            return ('ontouchstart' in window)
                || (navigator.maxTouchPoints > 0)
                || (navigator.msMaxTouchPoints > 0);
        }
    });
});
