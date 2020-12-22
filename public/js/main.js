(function ($) {

    'use strict';

    window.innocodeFlushCache = window.innocodeFlushCache || {};

    var selector = window.innocodeFlushCache.selector || '';
    var formSelector = '.' + selector + '__form';
    var onFormSubmit = function (event) {
        event.preventDefault();
        var $form = $(this);
        var $h1 = $form.closest('.wrap').find('h1');
        var $button = $form.find('.button');
        var $spinner = $form.find('.spinner');

        $h1.nextAll('.' + selector + '__notice').remove();
        $button.prop('disabled', true);
        $spinner.addClass('is-active');

        $.post($form.attr('action'), $form.serialize())
            .always(function (response) {
                var $notice;

                if (response.data) {
                    $notice = $('<div class="notice is-dismissible ' + selector + '__notice"><p>' + response.data + '</p></div>');

                    if (response.success) {
                        $notice.addClass('notice-success');
                    } else {
                        $notice.addClass('notice-error');
                    }

                    $h1.after($notice);
                }

                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            });
    };

    $(function () {
        $(formSelector).on('submit', onFormSubmit);
    });
})(jQuery);
