define([
    'uiComponent',
    'mage/translate',
], function (Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Rewards/guest-highlight',
            captionStartText : $t('You can earn'),
            captionAfterText: '',
            captionRegistrationText: $t('for registration!'),
            captionEndText: '',
            frontend_class: '',
            highlight: []
        },

        initObservable: function () {
            this._super().observe([ 'highlight' ]);
            if (this.highlight._latestValue.need_to_change_message === 1) {
                this.captionAfterText = $t('for making a purchase! Available for');
                this.captionRegistrationText = $t('registered');
                this.captionEndText = $t('customers only.');
            }

            return this;
        }
    });
});
