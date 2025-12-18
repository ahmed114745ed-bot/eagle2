console.log('✅ sidebar js loaded');

(function () {
    'use strict';

    let currentPopover = null;      // kept for backward compatibility (always top-most)
    let popoverTimeout = null;

    // NEW: support multiple (nested) popovers
    let popoverStack = [];          // [{ el, level }]
    let treeIdCounter = 1;

    // Check if sidebar is collapsed
    function isSidebarCollapsed() {
        return document.body.classList.contains('sidebar-collapse');
    }

    // NEW: RTL helper (for correct side positioning)
    function isRTL() {
        return document.documentElement.dir === 'rtl' || document.body.classList.contains('rtl');
    }

    // NEW: Assign ids to original trees so cloned links can reference them
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

    // NEW: Close popovers from a given nesting level (keeps parents)
    function closePopoversFrom(level) {
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
    }

    // NEW: Find the original tree for a toggle
    function resolveTreeFromToggle(toggle) {
        // If this is a cloned toggle inside a popover, it will have a target tree id
        const targetId = toggle && toggle.dataset ? toggle.dataset.targetTreeId : null;
        if (targetId) {
            return document.querySelector(`.crs-tree[data-crs-tree-id="${CSS.escape(targetId)}"]`);
        }
        // Otherwise it's a real toggle in the sidebar
        return toggle.closest('.crs-tree');
    }

    // NEW: Build popover items (direct children only); nested trees become "proxy toggles"
    function buildPopoverItemsFromSubmenu(popover, submenu) {
        const children = Array.from(submenu.children); // direct only (prevents deep nesting)

        children.forEach(child => {
            // nested tree item
            if (child.classList && child.classList.contains('crs-tree')) {
                const nestedToggle = child.querySelector(':scope > .crs-toggle');
                if (!nestedToggle) return;

                // Create a list item wrapper
                const li = document.createElement('li');
                li.className = 'crs-item';

                // Clone ONLY the toggle row (not the nested submenu)
                const clonedToggle = nestedToggle.cloneNode(true);

                // Mark it so we can open the next popover
                if (child.dataset && child.dataset.crsTreeId) {
                    clonedToggle.dataset.targetTreeId = child.dataset.crsTreeId;
                }

                li.appendChild(clonedToggle);
                popover.appendChild(li);
                return;
            }

            // normal leaf item (or any non-tree element)
            popover.appendChild(child.cloneNode(true));
        });
    }

    // UPDATED: createPopover supports nested popovers via `level`
    function createPopover(toggle, level = 0) {
        // Close any popovers at this level or deeper, but keep parents
        closePopoversFrom(level);

        const tree = resolveTreeFromToggle(toggle);
        if (!tree) return;

        const submenu = tree.querySelector(':scope > .crs-submenu');
        if (!submenu) return;

        const popover = document.createElement('div');
        popover.className = 'crs-popover';
        popover.dataset.level = String(level);

        // Clone submenu direct items (and convert nested submenus into proxy toggles)
        buildPopoverItemsFromSubmenu(popover, submenu);

        document.body.appendChild(popover);

        // Anchor position
        const anchorRect = toggle.getBoundingClientRect();
        const popoverRect = popover.getBoundingClientRect();

        let top, left;

        if (level === 0) {
            // Original behavior: below toggle (or above if overflow)
            top = anchorRect.bottom + 10;
            left = anchorRect.left + (anchorRect.width / 2) - (popoverRect.width / 2);

            // Adjust if popover goes off screen
            if (left < 10) left = 10;
            if (left + popoverRect.width > window.innerWidth - 10) {
                left = window.innerWidth - popoverRect.width - 10;
            }

            if (top + popoverRect.height > window.innerHeight - 10) {
                top = anchorRect.top - popoverRect.height - 10;
                popover.style.setProperty('--arrow-position', 'bottom');
            }
        } else {
            // NEW: Nested behavior: open to the side of the hovered item
            const gap = 10;

            top = anchorRect.top - 8;

            const rtl = isRTL();
            let preferredLeft = rtl
                ? (anchorRect.left - popoverRect.width - gap)
                : (anchorRect.right + gap);

            // If preferred side overflows, flip to the other side
            if (preferredLeft < 10) {
                preferredLeft = anchorRect.right + gap;
            }
            if (preferredLeft + popoverRect.width > window.innerWidth - 10) {
                preferredLeft = anchorRect.left - popoverRect.width - gap;
            }

            left = preferredLeft;

            // Clamp
            if (left < 10) left = 10;
            if (left + popoverRect.width > window.innerWidth - 10) {
                left = window.innerWidth - popoverRect.width - 10;
            }
            if (top < 10) top = 10;
            if (top + popoverRect.height > window.innerHeight - 10) {
                top = window.innerHeight - popoverRect.height - 10;
            }
        }

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';

        // Show popover
        setTimeout(() => {
            popover.classList.add('show');
        }, 10);

        // Track popover stack
        popoverStack.push({ el: popover, level });
        currentPopover = popover;

        // Handle popover link clicks:
        // - leaf: close all
        // - proxy toggle (has data-target-tree-id): open child popover instead
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

        // Add hover listeners to keep popovers open
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

        // Only show popover on click when sidebar is collapsed
        if (isSidebarCollapsed()) {
            console.log('✅ isSidebarCollapsed');
            if (isOpen) {
                closeAllPopovers();
            }
            else {
                createPopover(toggle, 0);
            }
            tree.classList.toggle('crs-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        } else {
            // Normal behavior when expanded - toggle submenu
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
            // Only show tooltip/popover when sidebar is collapsed
            if (isSidebarCollapsed()) {
                const isToggle = link.classList.contains('crs-toggle');

                // NEW: If hovering a toggle INSIDE a popover and it points to a nested tree,
                // open a child popover instead of making the current one long.
                if (inPopover && isToggle && link.dataset.targetTreeId) {
                    if (popoverTimeout) clearTimeout(popoverTimeout);

                    const parentPopover = link.closest('.crs-popover');
                    const parentLevel = parentPopover ? parseInt(parentPopover.dataset.level || '0', 10) : 0;

                    popoverTimeout = setTimeout(() => {
                        if (isSidebarCollapsed()) {
                            createPopover(link, parentLevel + 1);
                        }
                    }, 220);

                    return;
                }

                // Original behavior (sidebar items)
                if (!inPopover && isToggle) {
                    if (popoverTimeout) clearTimeout(popoverTimeout);

                    popoverTimeout = setTimeout(() => {
                        const tree = link.closest('.crs-tree');
                        if (isSidebarCollapsed()) {
                            createPopover(link, 0);
                        }
                    }, 300);
                } else if (!inPopover) {
                    // Tooltips only for leaf items in the sidebar (not inside popovers)
                    const title = link.querySelector('.crs-title');
                    if (title && !title.textContent.includes('...')) {
                        link.setAttribute('data-tooltip', title.textContent.trim());
                    }
                }
            } else {
                closeAllPopovers();
            }
        } else if (e.type === 'mouseleave' || e.type === 'blur') {
            if (popoverTimeout) clearTimeout(popoverTimeout);

            // If leaving a row inside a popover, close only deeper popovers (not the parent)
            if (inPopover) {
                const parentPopover = link.closest('.crs-popover');
                const parentLevel = parentPopover ? parseInt(parentPopover.dataset.level || '0', 10) : 0;

                setTimeout(() => {
                    // If not hovering any popover, close all; otherwise close deeper levels
                    const hoveringAny = popoverStack.some(p => p.el && p.el.matches(':hover'));
                    if (!hoveringAny) closeAllPopovers();
                    else closePopoversFrom(parentLevel + 1);
                }, 160);

                return;
            }

            // Original behavior for sidebar hover leaving
            setTimeout(() => {
                if (!currentPopover || !currentPopover.matches(':hover')) {
                    closeAllPopovers();
                }
            }, 100);
        }
    }

    // Handle popover hover to keep it open
    function handlePopoverHover(e) {
        if (e.type === 'mouseenter') {
            if (popoverTimeout) clearTimeout(popoverTimeout);
        } else if (e.type === 'mouseleave') {
            // Close only when leaving ALL popovers
            setTimeout(() => {
                const hoveringAny = popoverStack.some(p => p.el && p.el.matches(':hover'));
                if (!hoveringAny) closeAllPopovers();
            }, 140);
        }
    }

    // Add event listeners for hover interactions
    document.addEventListener('mouseenter', handleMenuItemHover, true);
    document.addEventListener('mouseleave', handleMenuItemHover, true);
    document.addEventListener('focus', handleMenuItemHover, true);
    document.addEventListener('blur', handleMenuItemHover, true);

    document.addEventListener('click', handleToggleClick);

    // Close popover when clicking outside
    document.addEventListener('click', (e) => {
        const isClickOnPopover = e.target.closest('.crs-popover');
        const isClickOnToggle = e.target.closest('.crs-toggle');

        if (!isClickOnPopover && !isClickOnToggle) {
            closeAllPopovers();
        }
    });

    // Listen for sidebar collapse/expand changes
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
