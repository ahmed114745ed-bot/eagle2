// custom-red-sidebar.js
// console.log('✅ sidebar js loaded');

(function () {
    'use strict';

    // Close all other open menus except the current one
    function closeOtherMenus(currentTree) {
        const openMenus = document.querySelectorAll('.crs-tree.crs-open');
        // console.log('📋 Open menus found:', openMenus.length);

        openMenus.forEach(function(tree) {
            if (tree !== currentTree) {
                // console.log('🔒 Closing menu:', tree);

                const submenu = tree.querySelector('.crs-submenu');
                const toggle = tree.querySelector('.crs-toggle');

                if (submenu) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    void submenu.offsetHeight;
                    submenu.style.maxHeight = '0px';

                    submenu.addEventListener('transitionend', function _h() {
                        submenu.style.removeProperty('max-height');
                        submenu.removeEventListener('transitionend', _h);
                    });
                }

                tree.classList.remove('crs-open');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    // Handle toggle click
    function handleToggleClick(e) {
        const toggle = e.target.closest('.crs-toggle');
        if (!toggle) return;

        e.preventDefault();
        // console.log('🖱️ Toggle clicked:', toggle);

        const tree = toggle.closest('.crs-tree');
        if (!tree) {
            // console.log('❌ No .crs-tree found');
            return;
        }

        const submenu = tree.querySelector('.crs-submenu');
        if (!submenu) {
            // console.log('❌ No .crs-submenu found');
            return;
        }

        const isOpen = tree.classList.contains('crs-open');
        // console.log('📂 Is currently open:', isOpen);

        if (isOpen) {
            // Close current menu
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            void submenu.offsetHeight;
            submenu.style.maxHeight = '0px';
            tree.classList.remove('crs-open');
            toggle.setAttribute('aria-expanded', 'false');

            submenu.addEventListener('transitionend', function _h() {
                submenu.style.removeProperty('max-height');
                submenu.removeEventListener('transitionend', _h);
            });
        } else {
            // Close other menus first
            // console.log('🔄 Closing other menus...');
            closeOtherMenus(tree);

            // Open current menu
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            tree.classList.add('crs-open');
            toggle.setAttribute('aria-expanded', 'true');

            submenu.addEventListener('transitionend', function _k() {
                submenu.style.maxHeight = 'none';
                submenu.removeEventListener('transitionend', _k);
            });
        }
    }

    document.addEventListener('click', handleToggleClick);

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.crs-submenu').forEach(function (sm) {
            const tree = sm.closest('.crs-tree');
            if (!tree || !tree.classList.contains('crs-open')) {
                sm.style.maxHeight = '0px';
            } else {
                sm.style.maxHeight = 'none';
            }
        });
    });

})();
