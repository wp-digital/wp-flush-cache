(function ($) {

    'use strict';

    window.innocodeFlushCache = window.innocodeFlushCache || {};

    var selector = window.innocodeFlushCache.selector || '';
    var formSelector = '.' + selector + '__form';
    var linkSelector = '.' + selector + '__link';
    var noticeSelector = '.' + selector + '__notice';
    var notice = function (response, $target) {
        var $notice;

        if (response.data) {
            $notice = $('<div class="notice ' + selector + '__notice"><p>' + response.data + '</p></div>');

            if (response.success) {
                $notice.addClass('notice-success');
            } else {
                $notice.addClass('notice-error');
            }

            $target.after($notice);
        }
    };
    var onFormSubmit = function (event) {
        event.preventDefault();
        var $form = $(this);
        var $h1 = $form.closest('.wrap').find('h1');
        var $button = $form.find('.button');
        var $spinner = $form.find('.spinner');

        $h1.nextAll(noticeSelector).remove();
        $button.prop('disabled', true);
        $spinner.addClass('is-active');

        $.post($form.attr('action'), $form.serialize())
            .always(function (response) {
                notice(response, $h1);
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            });
    };
    var onLinkClick = function (event) {
        event.preventDefault();
        var $link = $(this);
        var $h1 = $link.closest('.wrap').find('h1').nextAll('.wp-header-end');
        var $spinner = $link.next(linkSelector + '-spinner');

        $h1.nextAll(noticeSelector).remove();
        $link.hide();
        $spinner.show();

        $.get($link.attr('href'))
            .always(function (response) {
                notice(response, $h1);
                $spinner.hide();
                $link.show();
            });
    };

    $(function () {
        $(formSelector).on('submit', onFormSubmit);
        $(linkSelector).on('click', onLinkClick);
    });
})(jQuery);
