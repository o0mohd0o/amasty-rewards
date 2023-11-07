define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            visible: false,
            balance: 0,
            captionText: false
        },

        initObservable: function () {
            this._super().observe('visible balance captionText');

            return this;
        },

        initialize: function () {
            this._super();
            var rewardsData = customerData.get('rewards');

            if (rewardsData().balance) {
                this.balance(rewardsData().balance);
            }

            rewardsData.subscribe(function (rewardsData) {
                this.balance(rewardsData.balance);
            }.bind(this));

            this.visible(true);
        },
    });
});
