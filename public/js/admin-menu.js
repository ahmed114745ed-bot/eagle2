console.log('✅ sidebar js loaded');

(function () {
    'use strict';

    let currentPopover = null;
    let popoverTimeout = null;

    let popoverStack = [];
    let treeIdCounter = 1;

    let currentTooltip = null;
    let tooltipTimeout = null;

    let openChildTimer = null;
    let closeChildTimer = null;

    function isSidebarCollapsed() {
        return document.body.classList.contains('sidebar-collapse');
    }

    function isRTL() {
        return document.documentElement.dir === 'rtl' || document.body.classList.contains('rtl');
    }

    function indexTrees() {
        document.querySelectorAll('.crs-tree').forEach(tree => {
            if (!tree.dataset.crsTreeId) {
                tree.dataset.crsTreeId = String(treeIdCounter++);
            }
        });
    }

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

        document.querySelectorAll('.crs-tree.crs-open').forEach(function (tree) {
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

    function closeTooltip() {
        if (tooltipTimeout) {
            clearTimeout(tooltipTimeout);
            tooltipTimeout = null;
        }

        if (currentTooltip) {
            currentTooltip.classList.remove('show');
            const el = currentTooltip;
            currentTooltip = null;
            setTimeout(() => {
                if (el && el.parentNode) el.remove();
            }, 200);
        }
    }

    function createLeafTooltip(link) {
        closeTooltip();

        const titleEl = link.querySelector('.crs-title');
        if (!titleEl) return;

        const text = titleEl.textContent.trim();
        if (!text) return;

        const tip = document.createElement('div');
        tip.className = 'crs-tooltip';
        tip.textContent = text;

        document.body.appendChild(tip);

        const r = link.getBoundingClientRect();
        const tr = tip.getBoundingClientRect();
        const gap = 12;

        let top = r.top + (r.height / 2) - (tr.height / 2);

        const rtl = isRTL();
        let left;

        tip.classList.remove('from-left', 'from-right');

        if (rtl) {
            left = r.left - tr.width - gap;
            tip.classList.add('from-right');
        } else {
            left = r.right + gap;
            tip.classList.add('from-left');
        }

        if (left < 10) {
            left = r.right + gap;
            tip.classList.remove('from-right');
            tip.classList.add('from-left');
        }
        if (left + tr.width > window.innerWidth - 10) {
            left = r.left - tr.width - gap;
            tip.classList.remove('from-left');
            tip.classList.add('from-right');
        }

        if (top < 10) top = 10;
        if (top + tr.height > window.innerHeight - 10) top = window.innerHeight - tr.height - 10;

        tip.style.left = left + 'px';
        tip.style.top = top + 'px';

        setTimeout(() => tip.classList.add('show'), 10);
        currentTooltip = tip;
    }

    function closePopoversFrom(level) {
        if (openChildTimer) {
            clearTimeout(openChildTimer);
            openChildTimer = null;
        }
        if (closeChildTimer) {
            clearTimeout(closeChildTimer);
            closeChildTimer = null;
        }

        for (let i = popoverStack.length - 1; i >= 0; i--) {
            const p = popoverStack[i];
            if (p.level >= level) {
                p.el.classList.remove('show');
                const el = p.el;
                setTimeout(() => {
                    if (el && el.parentNode) el.remove();
                }, 250);
                popoverStack.pop();
            }
        }
        currentPopover = popoverStack.length ? popoverStack[popoverStack.length - 1].el : null;
    }

    function closeAllPopovers() {
        closePopoversFrom(0);
        closeTooltip();
    }

    function resolveTreeFromToggle(toggle) {
        const targetId = toggle && toggle.dataset ? toggle.dataset.targetTreeId : null;
        if (targetId) {
            const safe = (window.CSS && CSS.escape) ? CSS.escape(targetId) : targetId;
            return document.querySelector(`.crs-tree[data-crs-tree-id="${safe}"]`);
        }
        return toggle.closest('.crs-tree');
    }

    function buildPopoverItemsFromSubmenu(popover, submenu) {
        const children = Array.from(submenu.children);

        children.forEach(child => {
            if (child.classList && child.classList.contains('crs-tree')) {
                const nestedToggle = child.querySelector(':scope > .crs-toggle');
                if (!nestedToggle) return;

                const li = document.createElement('li');
                li.className = 'crs-item';

                const clonedToggle = nestedToggle.cloneNode(true);

                if (child.dataset && child.dataset.crsTreeId) {
                    clonedToggle.dataset.targetTreeId = child.dataset.crsTreeId;
                }

                li.appendChild(clonedToggle);
                popover.appendChild(li);
                return;
            }

            popover.appendChild(child.cloneNode(true));
        });
    }

    function createPopover(toggle, level = 0) {
        closeTooltip();
        closePopoversFrom(level);

        const tree = resolveTreeFromToggle(toggle);
        if (!tree) return;

        const submenu = tree.querySelector(':scope > .crs-submenu');
        if (!submenu) return;

        const popover = document.createElement('div');
        popover.className = 'crs-popover';
        popover.dataset.level = String(level);

        buildPopoverItemsFromSubmenu(popover, submenu);
        document.body.appendChild(popover);

        const anchorRect = toggle.getBoundingClientRect();
        const popoverRect = popover.getBoundingClientRect();
        const gap = 12;

        let top = anchorRect.top + (anchorRect.height / 2) - (popoverRect.height / 2);

        let left;
        popover.classList.remove('from-right');

        if (isRTL()) {
            left = anchorRect.left - popoverRect.width - gap;
            popover.classList.add('from-right');
        } else {
            left = anchorRect.right + gap;
        }

        if (left < 10) {
            left = anchorRect.right + gap;
            popover.classList.remove('from-right');
        }
        if (left + popoverRect.width > window.innerWidth - 10) {
            left = anchorRect.left - popoverRect.width - gap;
            popover.classList.add('from-right');
        }

        if (top < 10) top = 10;
        if (top + popoverRect.height > window.innerHeight - 10) {
            top = window.innerHeight - popoverRect.height - 10;
        }

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';

        setTimeout(() => {
            popover.classList.add('show');
        }, 10);

        popoverStack.push({ el: popover, level });
        currentPopover = popover;

        popover.querySelectorAll('.crs-link').forEach(link => {
            link.addEventListener('click', (e) => {
                const isProxyToggle = link.classList.contains('crs-toggle') && !!link.dataset.targetTreeId;

                if (isProxyToggle) {
                    e.preventDefault();
                    createPopover(link, level + 1);
                    return;
                }

                closeAllPopovers();
            });
        });

        popover.addEventListener('mouseenter', handlePopoverHover);
        popover.addEventListener('mouseleave', handlePopoverHover);
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

        if (isSidebarCollapsed()) {
            if (isOpen) {
                closeAllPopovers();
            } else {
                createPopover(toggle, 0);
            }
            tree.classList.toggle('crs-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        } else {
            closeAllPopovers();

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
    }

    function handleMenuItemHover(e) {
        const link = e.target.closest('.crs-link');
        if (!link) return;

        const inPopover = !!link.closest('.crs-popover');

        if (e.type === 'mouseenter' || e.type === 'focus') {
            if (isSidebarCollapsed()) {
                const isToggle = link.classList.contains('crs-toggle');

                if (inPopover && isToggle && link.dataset.targetTreeId) {
                    closeTooltip();

                    if (openChildTimer) clearTimeout(openChildTimer);
                    if (closeChildTimer) clearTimeout(closeChildTimer);

                    const parentPopover = link.closest('.crs-popover');
                    const parentLevel = parentPopover ? parseInt(parentPopover.dataset.level || '0', 10) : 0;

                    openChildTimer = setTimeout(() => {
                        if (isSidebarCollapsed()) createPopover(link, parentLevel + 1);
                    }, 80);

                    return;
                }

                if (!inPopover && isToggle) {
                    closeTooltip();

                    if (popoverTimeout) clearTimeout(popoverTimeout);

                    popoverTimeout = setTimeout(() => {
                        if (isSidebarCollapsed()) createPopover(link, 0);
                    }, 300);

                    return;
                }

                if (!inPopover && !isToggle) {
                    if (tooltipTimeout) clearTimeout(tooltipTimeout);
                    tooltipTimeout = setTimeout(() => {
                        if (isSidebarCollapsed()) createLeafTooltip(link);
                    }, 200);
                    return;
                }
            } else {
                closeAllPopovers();
            }
        }

        if (e.type === 'mouseleave' || e.type === 'blur') {
            if (popoverTimeout) clearTimeout(popoverTimeout);
            if (tooltipTimeout) clearTimeout(tooltipTimeout);

            closeTooltip();

            if (inPopover) {
                const parentPopover = link.closest('.crs-popover');
                const parentLevel = parentPopover ? parseInt(parentPopover.dataset.level || '0', 10) : 0;

                if (parentPopover && e.relatedTarget && parentPopover.contains(e.relatedTarget)) {
                    return;
                }

                if (openChildTimer) clearTimeout(openChildTimer);

                if (closeChildTimer) clearTimeout(closeChildTimer);
                closeChildTimer = setTimeout(() => {
                    const hoveringAny = popoverStack.some(p => p.el && p.el.matches(':hover'));
                    if (!hoveringAny) closeAllPopovers();
                    else closePopoversFrom(parentLevel + 1);
                }, 260);

                return;
            }

            setTimeout(() => {
                if (!currentPopover || !currentPopover.matches(':hover')) {
                    closeAllPopovers();
                }
            }, 100);
        }
    }

    function handlePopoverHover(e) {
        if (e.type === 'mouseenter') {
            if (popoverTimeout) clearTimeout(popoverTimeout);
            if (closeChildTimer) clearTimeout(closeChildTimer);
        } else if (e.type === 'mouseleave') {
            setTimeout(() => {
                const hoveringAny = popoverStack.some(p => p.el && p.el.matches(':hover'));
                if (!hoveringAny) closeAllPopovers();
            }, 140);
        }
    }

    document.addEventListener('mouseenter', handleMenuItemHover, true);
    document.addEventListener('mouseleave', handleMenuItemHover, true);
    document.addEventListener('focus', handleMenuItemHover, true);
    document.addEventListener('blur', handleMenuItemHover, true);

    document.addEventListener('click', handleToggleClick);

    document.addEventListener('click', (e) => {
        const isClickOnPopover = e.target.closest('.crs-popover');
        const isClickOnToggle = e.target.closest('.crs-toggle');

        if (!isClickOnPopover && !isClickOnToggle) {
            closeAllPopovers();
        }
    });

    window.addEventListener('DOMContentLoaded', function () {
        indexTrees();

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
