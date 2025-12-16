console.log('✅ sidebar js loaded');

(function () {
    'use strict';

    function animateSubmenuItems(submenu) {
        const items = submenu.querySelectorAll('.crs-item');

        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-15px)';

            setTimeout(() => {
                item.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 120);
        });
    }

    function resetSubmenuItems(submenu) {
        const items = submenu.querySelectorAll('.crs-item');

        items.forEach((item) => {
            item.style.transition = 'none';
            item.style.opacity = '0';
            item.style.transform = 'translateX(-15px)';
        });
    }

    function closeOtherMenus(currentTree) {
        const currentLevel = getMenuLevel(currentTree);

        document.querySelectorAll('.crs-tree.crs-open').forEach(function(tree) {
            if (tree !== currentTree && getMenuLevel(tree) === currentLevel) {
                const submenu = tree.querySelector('.crs-submenu');
                const toggle = tree.querySelector('.crs-toggle');

                if (submenu) {
                    resetSubmenuItems(submenu);
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

    function getMenuLevel(tree) {
        let level = 0;
        let parent = tree.parentElement;

        while (parent) {
            if (parent.classList && parent.classList.contains('crs-submenu')) {
                level++;
            }
            parent = parent.parentElement;
        }

        return level;
    }

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
            resetSubmenuItems(submenu);
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
            closeOtherMenus(tree);

            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            tree.classList.add('crs-open');
            toggle.setAttribute('aria-expanded', 'true');

            animateSubmenuItems(submenu);

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
                resetSubmenuItems(sm);
            } else {
                sm.style.maxHeight = 'none';
            }
        });
    });

})();
