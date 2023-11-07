define ([
    'jquery'
], function ($) {

    function reload(map, selectedValue) {
        if (map[selectedValue]) {
            $.each(map[selectedValue], function (fieldToUpdate, note) {
                var fieldElement = $('#row_' + fieldToUpdate),
                    noteElement = fieldElement.find('.note');

                if (!noteElement.length) {
                    noteElement = $('<p class="note"></p>').insertAfter(fieldElement.find('.tooltip'));
                }

                noteElement.html('<span>' + note + '</span>');
            });
        }
    }

    return function (config, element) {
        $(element).on('change', function (event) {
            reload(config.map, event.currentTarget.value);
        });
        reload(config.map, $(element).val());
    };
});
