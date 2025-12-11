// custom-red-sidebar.js
console.log('✅ notifications.js loaded');

(function () {
    'use strict';

    // يفعّل السلوك على عنصر toggle (event delegation)
    function handleToggleClick(e) {
        const toggle = e.target.closest('.crs-toggle');
        if (!toggle) return;

        e.preventDefault();

        const tree = toggle.closest('.crs-tree');
        if (!tree) return;

        const submenu = tree.querySelector('.crs-submenu');
        if (!submenu) return;

        const isOpen = tree.classList.contains('crs-open');

        if (isOpen) {
            // close: set maxHeight to current scrollHeight then to 0 to animate
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            // force repaint
            void submenu.offsetHeight;
            submenu.style.maxHeight = '0px';
            tree.classList.remove('crs-open');
            toggle.setAttribute('aria-expanded', 'false');
            // remove inline style after transition end
            submenu.addEventListener('transitionend', function _h() {
                submenu.style.removeProperty('max-height');
                submenu.removeEventListener('transitionend', _h);
            });
        } else {
            // open: set maxHeight to scrollHeight for animation
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            tree.classList.add('crs-open');
            toggle.setAttribute('aria-expanded', 'true');
            // remove inline max-height after transition to allow responsiveness
            submenu.addEventListener('transitionend', function _k() {
                // keep height auto after finished for responsiveness
                submenu.style.maxHeight = 'none';
                submenu.removeEventListener('transitionend', _k);
            });
        }
    }

    // initialize: attach delegation on document (works with PJAX/dynamic)
    document.addEventListener('click', handleToggleClick);

    // optional: on DOM ready, collapse any open submenu that has inline styles left
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.crs-submenu').forEach(function (sm) {
            // reset any leftover inline maxHeight unless parent is open
            const tree = sm.closest('.crs-tree');
            if (!tree || !tree.classList.contains('crs-open')) {
                sm.style.maxHeight = '0px';
            } else {
                // if open set none for responsiveness
                sm.style.maxHeight = 'none';
            }
        });
    });

    // helper: if you want to close others when opening one, you can enable:
    // document.addEventListener('click', function(e){ ... close others ... });

})();
