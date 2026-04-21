/* =====================================================================
   /gallery — trade + style pill filter. Swaps the iframe src with a
   short skeleton shimmer, pushes the new state into the URL via
   history.replaceState so refreshing + sharing keeps the current view.
===================================================================== */

(function () {
    'use strict';

    var iframe     = document.getElementById('preview-frame');
    var skeleton   = document.getElementById('preview-skeleton');
    var crumbTrade = document.getElementById('crumb-trade');
    var crumbStyle = document.getElementById('crumb-style');
    var fullscreen = document.getElementById('open-fullscreen');
    if (!iframe || !skeleton) return;

    var state = {
        trade: iframe.src.match(/\/mockups\/([a-z]+)-/)?.[1] || 'plumber',
        style: iframe.src.match(/-([a-z]+)\.html$/)?.[1] || 'modern'
    };

    function applyState(next) {
        if (next.trade === state.trade && next.style === state.style) return;
        state = next;

        var url = '/gallery/mockups/' + state.trade + '-' + state.style + '.html';

        // Show skeleton, fade iframe out.
        skeleton.classList.remove('is-hidden');
        iframe.classList.remove('is-loaded');

        // Actually swap.
        iframe.src = url;
        fullscreen.href = url;

        crumbTrade.textContent = capitalise(state.trade);
        crumbStyle.textContent = capitalise(state.style);

        // URL sync — no reload, just reflect what's onscreen.
        var params = new URLSearchParams(window.location.search);
        params.set('trade', state.trade);
        params.set('style', state.style);
        window.history.replaceState(null, '', '/gallery?' + params.toString());

        updatePills();
    }

    function updatePills() {
        document.querySelectorAll('.pill[data-trade]').forEach(function (b) {
            var on = b.getAttribute('data-trade') === state.trade;
            b.classList.toggle('is-selected', on);
            b.setAttribute('aria-checked', on ? 'true' : 'false');
        });
        document.querySelectorAll('.pill[data-style]').forEach(function (b) {
            var on = b.getAttribute('data-style') === state.style;
            b.classList.toggle('is-selected', on);
            b.setAttribute('aria-checked', on ? 'true' : 'false');
        });
    }

    function capitalise(s) {
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    // iframe hides the skeleton once it finishes loading. Works on first
    // load too — iframe emits 'load' whenever src changes.
    iframe.addEventListener('load', function () {
        skeleton.classList.add('is-hidden');
        iframe.classList.add('is-loaded');
    });
    // Flip it on first render in case the iframe was already in cache.
    if (iframe.complete) {
        skeleton.classList.add('is-hidden');
        iframe.classList.add('is-loaded');
    }

    // Pill click handlers.
    document.querySelectorAll('.pill[data-trade]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyState({ trade: btn.getAttribute('data-trade'), style: state.style });
        });
    });
    document.querySelectorAll('.pill[data-style]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyState({ trade: state.trade, style: btn.getAttribute('data-style') });
        });
    });

    // Back/forward buttons should re-run applyState from the new URL.
    window.addEventListener('popstate', function () {
        var params = new URLSearchParams(window.location.search);
        applyState({
            trade: params.get('trade') || state.trade,
            style: params.get('style') || state.style
        });
    });
})();
