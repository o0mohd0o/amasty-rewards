define([
    'Magento_Ui/js/form/components/fieldset',
    'uiRegistry',
    'Magento_Ui/js/lib/view/utils/async',
    'jquery'
], function (Fieldset, registry, async, $) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            initializeFieldsetDataByDefault: false,
            actionComments: {
                'ordercompleted': $.mage.__('Purchase is made bonus for order'),
                'subscription': $.mage.__('Newsletter subscription bonus'),
                'birthday': $.mage.__('Birthday points'),
                'moneyspent': $.mage.__('Spending every $X amount bonus'),
                'registration': $.mage.__('Registration bonus'),
                'visit': $.mage.__('Inactive for a long time bonus'),
                'review': $.mage.__('Review written bonus'),
            },
            listens: {
                '${ $.parentName }.actions.action:value': 'onChange'
            },
            modules: {
                defaultLabel: '${ $.parentName }.labels.store_labels[0]'
            }
        },

        initialize: function () {
            this._super();

            async.async('input[name="store_labels[0]"]', function () {
                registry.get(this.parentName + '.actions.action', function (component) {
                    this.setDefaultValue(component.value(), true);
                }.bind(this));
            }.bind(this));
        },

        onChange: function (value) {
            this.setDefaultValue(value);
        },

        setDefaultValue: function (value, init = false) {
            require(['jquery'], function ($) {
                var labels = $('input[name*=store_labels]').get();

                if (labels.length) {
                    for (var label in labels) {
                        if (labels.hasOwnProperty(label)
                            && this.actionComments.hasOwnProperty(value)
                        ) {
                            if (label == 0 && this.defaultLabel()) {
                                if (init && this.defaultLabel().value()) {
                                    continue;
                                }
                                this.defaultLabel().value(this.actionComments[value]);
                            } else {
                                if (init && labels[label].value) {
                                    continue;
                                }
                                labels[label].value = this.actionComments[value];
                            }
                        }
                    }
                }
            }.bind(this));
        }
    });
});
