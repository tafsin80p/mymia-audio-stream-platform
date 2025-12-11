(function($) {
    'use strict';

    let isLoading = false;

    function initNav(context) {
        const $nav = $('.nymia-dashboard-nav', context || document);
        if (!$nav.length) {
            return;
        }

        $nav.off('click.nymiaNav').on('click.nymiaNav', 'a', function(event) {
            const $link = $(this);
            if ($link.hasClass('active') || isLoading || event.metaKey || event.ctrlKey) {
                return;
            }

            event.preventDefault();
            const url = $link.attr('href');
            if (!url) {
                return;
            }
            loadPage(url, $link, { pushState: true });
        });
    }

    function setLoadingState($nav, $link, state) {
        isLoading = state;
        $nav.toggleClass('nymia-nav-loading', state);
        if ($link && $link.length) {
            $link.toggleClass('nymia-nav-link-loading', state);
        }
    }

    function loadPage(url, $link, options = {}) {
        const $nav = $('.nymia-dashboard-nav').first();
        if (!$nav.length) {
            window.location.href = url;
            return;
        }

        setLoadingState($nav, $link, true);

        $.ajax({
            url,
            method: 'GET',
            dataType: 'html',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function(response) {
            const $response = $('<div>').append($.parseHTML(response));
            const $newWrap = $response.find('.wrap.nymia-stripe-wrap').first();
            const $currentWrap = $('.wrap.nymia-stripe-wrap').first();

            if (!$newWrap.length || !$currentWrap.length) {
                window.location.href = url;
                return;
            }

            $currentWrap.replaceWith($newWrap);

            if (options.pushState) {
                history.pushState({ url }, '', url);
            }

            initNav();
            $(document).trigger('nymiaAdminPageLoaded');
        }).fail(function() {
            window.location.href = url;
        }).always(function() {
            setLoadingState($nav, $link, false);
        });
    }

    $(document).ready(function() {
        initNav();

        if (!window.history.state || !window.history.state.url) {
            history.replaceState({ url: window.location.href }, '', window.location.href);
        }

        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.url) {
                loadPage(event.state.url, null, { pushState: false });
            }
        });
    });
})(jQuery);

