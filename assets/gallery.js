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
    function markLoaded() {
        skeleton.classList.add('is-hidden');
        iframe.classList.add('is-loaded');
    }
    iframe.addEventListener('load', markLoaded);
    // Safety net: if 'load' already fired before JS attached (cached mockup).
    try {
        if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') {
            markLoaded();
        }
    } catch (_) { /* cross-origin would throw — ignore */ }
    // Last-ditch: reveal after 4s even if something is wrong so the user
    // isn't stuck watching a shimmer.
    setTimeout(markLoaded, 4000);

    // Pill click + keyboard arrow nav (radiogroup semantics).
    function wirePillGroup(selector, updater) {
        var pills = Array.prototype.slice.call(document.querySelectorAll(selector));
        pills.forEach(function (btn, i) {
            btn.addEventListener('click', function () { updater(btn); });
            btn.addEventListener('keydown', function (e) {
                if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
                e.preventDefault();
                var dir = e.key === 'ArrowRight' ? 1 : -1;
                var next = pills[(i + dir + pills.length) % pills.length];
                next.focus();
                updater(next);
            });
        });
    }
    wirePillGroup('.pill[data-trade]', function (btn) {
        applyState({ trade: btn.getAttribute('data-trade'), style: state.style });
    });
    wirePillGroup('.pill[data-style]', function (btn) {
        applyState({ trade: state.trade, style: btn.getAttribute('data-style') });
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
