/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation'
], function ($, registry) {
    'use strict';

    window.block_amrewards  = registry.get('customer_form.areas.block_amrewards.block_amrewards');
    window.block_amrewards_history  = registry.get('customer_form.areas.block_amrewardsHistory.block_amrewardsHistory');

    actionChange();

    $.widget('mage.newRewardsDialog', {
        _create: function () {
            var widget = this;
            var newRewardsForm = $('#new_rewards_form');
            newRewardsForm.mage('validation', {
                errorPlacement: function (error, element) {
                    error.insertAfter(element.is('#new_rewards_parent') ?
                        $('#new_rewards_parent-suggest').closest('.mage-suggest') :
                        element);
                },
                ignore: '.ignore-validate'
            }).on('highlight.validate', function (e) {
                var options = $(this).validation('option');
                if ($(e.target).is('#new_rewards_parent')) {
                    options.highlight($('#new_rewards_parent-suggest').get(0),
                        options.errorClass, options.validClass || '');
                }
            });
            this.element.modal({
                type: 'slide',
                modalClass: 'mage-new-rewards-dialog form-inline',
                title: $.mage.__('Add or Deduct Points'),
                closed: function () {
                    $('#new_rewards_messages').empty();
                    $('#new_rewards_amount').val('');
                    $('#new_rewards_amount').focus();
                    $('#new_rewards_comment').val('');
                },
                buttons: [{
                    text: $.mage.__('Apply'),
                    class: 'action-primary',
                    click: function (e) {
                        if (!newRewardsForm.valid()) {
                            return;
                        }
                        var thisButton = $(e.currentTarget);
                        thisButton.prop('disabled', true);
                        var postData = {
                            amount: $('#new_rewards_amount').val(),
                            comment: $('#new_rewards_comment').val(),
                            action: $('#new_rewards_action').val(),
                            expiration: {
                                is_expire: $('#new_rewards_expiration_behavior').val(),
                                days: $('#new_rewards_expiration_period').val()
                            },
                            form_key: FORM_KEY,
                            customer_id: widget.options.customerId,
                            return_session_messages_only: 1,
                            visible_for_customer: $('#visible_for_customer').val()
                        };

                        $.ajax({
                            type: 'POST',
                            url: widget.options.saveCategoryUrl,
                            data: postData,
                            dataType: 'json',
                            context: $('body'),
                            success: function (data) {
                                if (!data.error) {
                                    $('#new_rewards_amount, #new_rewards_comment').val('');
                                    $(widget.element).modal('closeModal');
                                } else {
                                    $('#new_rewards_messages').html(data.messages);
                                }
                            },
                            complete: function () {
                                thisButton.prop('disabled', false);
                                window.block_amrewards.loadData();
                                window.block_amrewards_history.loadData();
                            }
                        });
                    }
                }]
            });
        }
    });

    function actionChange() {
        if ($('#new_rewards_action').val() === 'add') {
            $('.field-new_rewards_expiration_behavior').show();
            expirationBehaviorChange();
        } else {
            $('.field-new_rewards_expiration_behavior').hide();
            $('.field-new_rewards_expiration_period').hide();
            $('#new_rewards_expiration_period').addClass('ignore-validate');
        }
    }

    function expirationBehaviorChange() {
        if ($('#new_rewards_expiration_behavior').val() === '1') {
            $('.field-new_rewards_expiration_period').show();
            $('#new_rewards_expiration_period').removeClass('ignore-validate');
        } else {
            $('.field-new_rewards_expiration_period').hide();
            $('#new_rewards_expiration_period').addClass('ignore-validate');
        }
    }

    $('#new_rewards_action').on('change', actionChange);

    $('#new_rewards_expiration_behavior').on('change', expirationBehaviorChange);

    return $.mage.newRewardsDialog;
});
