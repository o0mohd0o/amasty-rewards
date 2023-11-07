define([
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mageUtils',
        'mage/url',
        'mage/translate'
    ], function (customer, urlBuilder, utils, url, $t) {
        'use strict';

        return {

            /**
             * @param {String} points
             * @return {*}
             */
            getRewardsUrl: function (points) {
                var params = {},
                    url = {
                        'customer': '/carts/mine/points/' + encodeURIComponent(points)
                    };

                return this.getUrl(url, params);
            },

            /**
             * @return {String}
             */
            getCheckoutMethod: function () {
                return 'customer';
            },

            /**
             * Get url for service.
             *
             * @param {*} url
             * @param {*} urlParams
             * @return {String|*}
             */
            getUrl: function (url, urlParams) {
                var finalUrl;

                if (utils.isEmpty(url)) {
                    return $t('Provided service call does not exist.');
                }

                if (!utils.isEmpty(url['default'])) {
                    finalUrl = url['default'];
                } else {
                    finalUrl = url[this.getCheckoutMethod()];
                }

                return urlBuilder.createUrl(finalUrl, urlParams);
            }
        };
    }
);
