define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';
    var updateUrl = null;

    function checkPeriodVisibility() {
        if ($('#rewards_reports_date_range').val() === "0") {
            $('#rewards_reports_date_from, #rewards_reports_date_to').parent().show().find('*').show();
        } else {
            $('#rewards_reports_date_from, #rewards_reports_date_to').parent().hide();
        }
    }

    function refresh() {
        $.ajax({
            showLoader: true,
            url: updateUrl,
            dataType: 'JSON',
            data: $('.entry-edit.form-inline :input').serializeArray(),
            type: "POST",
            success: function (response) {
                if (response) {
                    if (response.type === 'error' || response.type === 'warning') {
                        $('.amrewards-report-statistics, .amrewards-report-chart').hide();
                        $('.amrewards-report-error').text(response.message).show();

                        if (response.type === 'warning') {
                            $('.amrewards-report-error').removeClass('message-error error');
                            $('.amrewards-report-error').addClass('message-info info');
                        } else if (response.type === 'error') {
                            $('.amrewards-report-error').removeClass('message-info info');
                            $('.amrewards-report-error').addClass('message-error error');
                        }
                    } else if (response.type === 'success') {
                        $('.amrewards-report-statistics, .amrewards-report-chart').show();
                        $('.amrewards-report-error').hide();
                        setData(response.data);
                    }
                }
            }
        });
    }

    function setData(data) {
        $('[data-amrewards-js="rewarded-points"]').text(data.total.rewarded);
        $('[data-amrewards-js="redeemed-points"]').text(data.total.redeemed);
        $('[data-amrewards-js="average-rewarded"]').text(data.average.rewarded);
        $('[data-amrewards-js="average-redeemed"]').text(data.average.redeemed);
        $('[data-amrewards-js="total-expired"]').text(data.total.expired);

        amRewardsReportChart.dataProvider = data.graph;
        amRewardsReportChart.parseData();
        amRewardsReportChart.zoomOut()
    }

    return function (config) {
        updateUrl = config.ajaxUrl;

        checkPeriodVisibility();

        $('#rewards_reports_date_range').on('change', checkPeriodVisibility);
        $('#rewards_reports_submit').on('click', refresh);

        $(document).ready(function() {
            refresh();
        });
    }
});
