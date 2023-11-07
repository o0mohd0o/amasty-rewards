define([
    'Magento_Ui/js/form/components/fieldset',
    'uiRegistry',
    'jquery'
], function (Fieldset, registry, $) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            conditionActions: [],
            listens: {
                '${ $.parentName }.actions.action:value': 'onChange'
            }
        },

        initialize: function () {
            this._super();
            registry.get(this.parentName + '.actions.action', function (component) {
                this.checkVisibility(component.value());
            }.bind(this));
        },

        onChange: function (value) {
            this.checkVisibility(value);
        },

        checkVisibility: function (value) {
            if (_.contains(this.conditionActions, value)) {
                this.visible(true);
            } else {
                this.visible(false);
            }
        }
    });
});
