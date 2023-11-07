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
            template: 'Amasty_Rewards/guest-highlight',
            captionStartText: $t('You can earn'),
            captionAfterText: '',
            captionRegistrationText: $t('for registration!'),
            captionEndText: '',
            productId: 0,
            refreshUrl: false,
            loader: false,
            formSelector: '#product_addtocart_form',
            frontend_class: '',
            highlight: {
                visible: false
            }
        },

        initObservable: function () {
            this._super().observe([ 'highlight', 'loader' ]);
            this.updateData();
            $(this.formSelector).change(this.updateData.bind(this));

            return this;
        },

        hide: function () {
            this.highlight({ 'visible':false });

            return this;
        },

        updateData: function () {
            if (xhr) {
                xhr.abort();
            }
            this.hide().loader(true);

            xhr = storage.post(this.refreshUrl,
                JSON.stringify({
                    page: 0,
                    productId: this.productId,
                    attributes: $(this.formSelector).serialize()
                }),
                false
            ).done(function (result) {
                if (result) {
                    if (result.need_to_change_message === 1) {
                        this.captionAfterText = $t('for making a purchase! Available for');
                        this.captionRegistrationText = $t('registered');
                        this.captionEndText = $t('customers only.');
                    }
                    this.highlight(result);
                }
            }.bind(this)).always(function () {
                this.loader(false);
                xhr = null;
            }.bind(this));
        }
    });
});
