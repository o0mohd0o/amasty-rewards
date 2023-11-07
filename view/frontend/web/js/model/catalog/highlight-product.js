define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'mage/storage',
], function ($, Component, $t, storage) {
    'use strict';
    var xhr = null;

    return Component.extend({
        defaults: {
            template: 'Amasty_Rewards/highlight',
            captionEndText: $t('for buying this product!'),
            captionStartText : $t('You can earn'),
            productId : 0,
            refreshUrl: false,
            loader: false,
            formSelector: '#product_addtocart_form',
            frontend_class: '',
            highlight: {
                visible: false
            }
        },

        initObservable: function () {
            this._super().observe(['highlight', 'loader']);
            this.updateData();
            $(this.formSelector).change(this.updateData.bind(this));

            return this;
        },

        hide: function () {
            this.highlight({'visible':false});

            return this;
        },

        updateData: function () {
            if (xhr) {
                xhr.abort();
            }
            this.hide().loader(true);

            xhr = storage.post(this.refreshUrl,
                JSON.stringify({
                    productId: this.productId,
                    attributes: $(this.formSelector).serialize()
                }),
                false
            ).done(function (result) {
                if (result) {
                    this.highlight(result);
                }
            }.bind(this)).always(function () {
                this.loader(false);
                xhr = null;
            }.bind(this));
        },
    });
});
