define([
    'jquery',
    'uiComponent',
    'mage/translate',
    'mage/storage',
], function ($, Component, $t, storage) {
    'use strict';
    var xhr = {};

    return Component.extend({
        defaults: {
            template: 'Amasty_Rewards/highlight-category',
            captionEndText: '',
            captionStartText: $t('Earn'),
            productId: 0,
            refreshUrl: false,
            loader: false,
            formSelector: false,
            frontend_class: '',
            highlight: {
                visible: false
            }
        },

        initObservable: function () {
            this._super().observe(['highlight', 'loader']);

            if (this.refreshUrl) {
                this.updateData();
                $(this.formSelector).change(this.updateData.bind(this));
            }

            return this;
        },

        hide: function () {
            this.highlight({'visible': false});

            return this;
        },

        updateData: function () {
            if (xhr.hasOwnProperty(this.productId)) {
                xhr[this.productId].abort();
            }
            this.hide().loader(true);

            xhr[this.productId] = storage.post(this.refreshUrl,
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
                delete xhr[this.productId];
            }.bind(this));
        },
    });
});
