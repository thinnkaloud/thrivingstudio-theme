// A script to handle the dark mode toggle functionality.

// Disable and unregister any existing Service Worker to prevent stale caches
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            // Unregister all existing registrations
            const registrations = await navigator.serviceWorker.getRegistrations();
            for (const reg of registrations) {
                try { await reg.unregister(); } catch (e) {}
            }

            // Clear all Cache Storage entries
            if (window.caches && caches.keys) {
                try {
                    const keys = await caches.keys();
                    await Promise.all(keys.map((k) => caches.delete(k)));
                } catch (e) {}
            }
        } catch (e) {
            // ignore
        }
    });
}

// Icons
const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
const mobileThemeToggleDarkIcon = document.getElementById('mobile-theme-toggle-dark-icon');
const mobileThemeToggleLightIcon = document.getElementById('mobile-theme-toggle-light-icon');

// Check for saved theme in localStorage and apply it
if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
    if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
    if (mobileThemeToggleLightIcon) mobileThemeToggleLightIcon.classList.remove('hidden');
} else {
    document.documentElement.classList.remove('dark');
    if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
    if (mobileThemeToggleDarkIcon) mobileThemeToggleDarkIcon.classList.remove('hidden');
}

// Theme toggle function
function toggleTheme() {
    // toggle icons inside button
    if (themeToggleDarkIcon) themeToggleDarkIcon.classList.toggle('hidden');
    if (themeToggleLightIcon) themeToggleLightIcon.classList.toggle('hidden');
    if (mobileThemeToggleDarkIcon) mobileThemeToggleDarkIcon.classList.toggle('hidden');
    if (mobileThemeToggleLightIcon) mobileThemeToggleLightIcon.classList.toggle('hidden');

    // if set via local storage previously
    if (localStorage.getItem('color-theme')) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        }

    // if NOT set via local storage previously
    } else {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    }
}

const themeToggleBtn = document.getElementById('theme-toggle');
const mobileThemeToggleBtn = document.getElementById('mobile-theme-toggle');

if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', toggleTheme);
}

if (mobileThemeToggleBtn) {
    mobileThemeToggleBtn.addEventListener('click', toggleTheme);
}

// Mobile menu toggle (robust init even if DOMContentLoaded already fired)
function tsSetupMobileMenu() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (!mobileMenuButton || !mobileMenu) return;
    if (mobileMenuButton._tsMenuBound) return;
    mobileMenuButton._tsMenuBound = true;

    const onClick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        mobileMenu.classList.toggle('hidden');
        const openIcon = document.querySelector('.js-mobile-menu-open-icon');
        const closeIcon = document.querySelector('.js-mobile-menu-close-icon');
        if (openIcon && closeIcon) {
            openIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        }
        const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
        mobileMenuButton.setAttribute('aria-expanded', String(!isExpanded));
    };

    mobileMenuButton.addEventListener('click', onClick);
    mobileMenuButton.addEventListener('touchstart', function() {}, { passive: true });
}

function tsRunWhenReady(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn, { once: true });
        window.addEventListener('load', fn, { once: true });
    } else {
        fn();
    }
}

tsRunWhenReady(tsSetupMobileMenu);

function tsSetupSingleRailRelease() {
    const article = document.querySelector('.ts-single-article');
    const widgetShell = document.querySelector('.ts-single-rail-widgets-shell');
    const tocShell = document.querySelector('.ts-single-toc-shell');

    if (!article || !widgetShell || !tocShell) return;
    if (article._tsRailReleaseBound) return;

    article._tsRailReleaseBound = true;
    article.classList.add('ts-single-rail-release-ready');

    const update = function() {
        if (window.innerWidth < 1100) {
            article.classList.remove('ts-single-rail-release-active');
            return;
        }

        const outlineTop = tocShell.getBoundingClientRect().top;
        const releaseStart = window.innerHeight - 24;

        article.classList.toggle('ts-single-rail-release-active', outlineTop <= releaseStart);
    };

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    window.addEventListener('load', update);
    window.addEventListener('pageshow', update);
    update();
}

tsRunWhenReady(tsSetupSingleRailRelease);

function tsSetupSingleTocActiveState() {
    const toc = document.querySelector('.ts-single-toc');
    const links = toc ? Array.from(toc.querySelectorAll('.ts-single-toc-list a[href^="#"]')) : [];

    if (!toc || links.length === 0) return;
    if (toc._tsActiveStateBound) return;

    const targets = links
        .map((link) => {
            const rawId = link.getAttribute('href').slice(1);
            if (!rawId) return null;

            const id = decodeURIComponent(rawId);
            const target = document.getElementById(id);
            return target ? { link, target } : null;
        })
        .filter(Boolean);

    if (targets.length === 0) return;

    toc._tsActiveStateBound = true;

    const setActiveLink = function(activeLink) {
        links.forEach((link) => {
            const isActive = link === activeLink;
            link.classList.toggle('ts-single-toc-link-active', isActive);
            link.parentElement?.classList.toggle('ts-single-toc-item-active', isActive);

            if (isActive) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const update = function() {
        const offset = Math.max(96, Math.round(window.innerHeight * 0.18));
        let active = targets[0];

        targets.forEach((item) => {
            if (item.target.getBoundingClientRect().top <= offset) {
                active = item;
            }
        });

        setActiveLink(active.link);
    };

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    window.addEventListener('load', update);
    window.addEventListener('pageshow', update);
    window.setInterval(update, 250);
    update();
}

tsRunWhenReady(tsSetupSingleTocActiveState);

function tsSetupQuoteCarousels() {
    const carousels = document.querySelectorAll('[data-quote-carousel]');

    carousels.forEach((carousel) => {
        if (carousel._tsQuoteCarouselBound) return;

        const track = carousel.querySelector('[data-quote-track]');
        const slides = Array.from(carousel.querySelectorAll('[data-quote-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-quote-dot]'));
        const prevBtn = carousel.querySelector('[data-quote-prev]');
        const nextBtn = carousel.querySelector('[data-quote-next]');

        if (!track || slides.length === 0) return;

        carousel._tsQuoteCarouselBound = true;
        let activeIndex = 0;
        let ticking = false;

        const canScroll = () => track.scrollWidth > track.clientWidth + 2;

        const nearestIndex = () => {
            const trackRect = track.getBoundingClientRect();
            const trackStart = trackRect.left;
            let closestIndex = 0;
            let closestDistance = Infinity;

            slides.forEach((slide, index) => {
                const slideRect = slide.getBoundingClientRect();
                const distance = Math.abs(slideRect.left - trackStart);

                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestIndex = index;
                }
            });

            return closestIndex;
        };

        const setActive = (index) => {
            activeIndex = Math.max(0, Math.min(index, slides.length - 1));

            dots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === activeIndex;
                dot.classList.toggle('is-active', isActive);

                if (isActive) {
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.removeAttribute('aria-current');
                }
            });
        };

        const updateControls = () => {
            const scrollable = canScroll();
            const atStart = track.scrollLeft <= 2;
            const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;

            carousel.classList.toggle('is-static', !scrollable);

            if (prevBtn) prevBtn.disabled = !scrollable || atStart;
            if (nextBtn) nextBtn.disabled = !scrollable || atEnd;

            setActive(nearestIndex());
        };

        const requestUpdate = () => {
            if (ticking) return;

            ticking = true;
            window.requestAnimationFrame(() => {
                ticking = false;
                updateControls();
            });
        };

        const scrollToIndex = (index) => {
            const target = slides[Math.max(0, Math.min(index, slides.length - 1))];
            if (!target) return;

            target.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'nearest',
                inline: 'start'
            });
        };

        if (prevBtn) {
            prevBtn.addEventListener('click', () => scrollToIndex(activeIndex - 1));
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => scrollToIndex(activeIndex + 1));
        }

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                scrollToIndex(Number(dot.dataset.quoteIndex || 0));
            });
        });

        track.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                scrollToIndex(activeIndex - 1);
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                scrollToIndex(activeIndex + 1);
            }
        });

        track.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate);
        window.addEventListener('load', requestUpdate);
        window.addEventListener('pageshow', requestUpdate);
        updateControls();
    });
}

tsRunWhenReady(tsSetupQuoteCarousels);

function tsCopyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    }

    return new Promise((resolve, reject) => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            const copied = document.execCommand('copy');
            document.body.removeChild(textarea);
            copied ? resolve() : reject(new Error('Copy command failed'));
        } catch (error) {
            document.body.removeChild(textarea);
            reject(error);
        }
    });
}

function tsSetupSingleQuoteCard() {
    const actionRoot = document.querySelector('[data-quote-actions]');
    const lightbox = document.querySelector('[data-quote-lightbox]');

    if (actionRoot && !actionRoot._tsQuoteActionsBound) {
        actionRoot._tsQuoteActionsBound = true;

        const quoteText = actionRoot.dataset.quoteText || document.title;
        const quoteUrl = actionRoot.dataset.quoteUrl || window.location.href;
        const status = actionRoot.querySelector('[data-quote-status]');
        let statusTimeout;

        const setStatus = (message, isError = false) => {
            if (!status) return;

            window.clearTimeout(statusTimeout);
            status.textContent = message;
            status.classList.toggle('is-error', isError);
            statusTimeout = window.setTimeout(() => {
                status.textContent = '';
                status.classList.remove('is-error');
            }, 2800);
        };

        actionRoot.querySelectorAll('[data-quote-copy]').forEach((button) => {
            button.addEventListener('click', async () => {
                const copyType = button.dataset.quoteCopy;
                const value = copyType === 'link' ? quoteUrl : quoteText;

                try {
                    await tsCopyText(value);
                    setStatus(copyType === 'link' ? 'Link copied.' : 'Quote copied.');
                } catch (error) {
                    setStatus('Copy failed. Please try again.', true);
                }
            });
        });

        const shareButton = actionRoot.querySelector('[data-quote-share]');

        if (shareButton) {
            shareButton.addEventListener('click', async () => {
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: document.title,
                            text: quoteText,
                            url: quoteUrl
                        });
                        setStatus('Share sheet opened.');
                        return;
                    } catch (error) {
                        if (error && error.name === 'AbortError') {
                            return;
                        }
                    }
                }

                try {
                    await tsCopyText(quoteUrl);
                    setStatus('Link copied for sharing.');
                } catch (error) {
                    setStatus('Sharing is not available here.', true);
                }
            });
        }
    }

    if (lightbox && !lightbox._tsQuoteLightboxBound) {
        lightbox._tsQuoteLightboxBound = true;

        const openButtons = document.querySelectorAll('[data-quote-lightbox-open]');
        const closeButtons = lightbox.querySelectorAll('[data-quote-lightbox-close]');
        const closeButton = lightbox.querySelector('.ts-quote-lightbox-close');
        let previousFocus = null;

        const openLightbox = () => {
            previousFocus = document.activeElement;
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.classList.add('ts-quote-lightbox-open');

            if (closeButton) {
                closeButton.focus();
            }
        };

        const closeLightbox = () => {
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('ts-quote-lightbox-open');

            if (previousFocus && typeof previousFocus.focus === 'function') {
                previousFocus.focus();
            }
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', openLightbox);
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', closeLightbox);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
                closeLightbox();
            }
        });
    }
}

tsRunWhenReady(tsSetupSingleQuoteCard);

// Category Menu Dropdown Functionality - Industry Standard
document.addEventListener('DOMContentLoaded', function() {
    const categoryMenuItems = document.querySelectorAll('.category-menu-list .has-dropdown');
    let hoverTimeout;
    
    categoryMenuItems.forEach(function(item) {
        const dropdown = item.querySelector('.dropdown-menu');
        const link = item.querySelector('a');
        
        if (!dropdown || !link) return;
        
        // Desktop: Show on hover with smooth transition
        item.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            // Close other dropdowns
            categoryMenuItems.forEach(function(otherItem) {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            // Show current dropdown
            item.classList.add('active');
        });
        
        // Keep dropdown open when hovering over it
        dropdown.addEventListener('mouseenter', function() {
            clearTimeout(hoverTimeout);
            item.classList.add('active');
        });
        
        // Hide dropdown when mouse leaves both item and dropdown
        item.addEventListener('mouseleave', function(e) {
            // Check if mouse is moving to dropdown (relatedTarget)
            const relatedTarget = e.relatedTarget;
            if (relatedTarget && (dropdown.contains(relatedTarget) || item.contains(relatedTarget))) {
                return; // Don't hide if moving to dropdown or staying within item
            }
            hoverTimeout = setTimeout(function() {
                item.classList.remove('active');
            }, 200); // Increased delay to allow moving to dropdown through gap
        });
        
        dropdown.addEventListener('mouseleave', function(e) {
            // Check if mouse is moving back to item
            const relatedTarget = e.relatedTarget;
            if (relatedTarget && item.contains(relatedTarget)) {
                return; // Don't hide if moving back to item
            }
            hoverTimeout = setTimeout(function() {
                item.classList.remove('active');
            }, 200);
        });
        
        // Mobile/Touch: Toggle on click
        link.addEventListener('click', function(e) {
            if (window.innerWidth < 768) { // Mobile breakpoint
                e.preventDefault();
                const isActive = item.classList.contains('active');
                
                // Close other dropdowns
                categoryMenuItems.forEach(function(otherItem) {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                item.classList.toggle('active', !isActive);
            }
        });
    });
    
    // Close dropdowns when clicking outside (mobile and desktop)
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.has-dropdown')) {
            categoryMenuItems.forEach(function(item) {
                item.classList.remove('active');
            });
        }
    });
    
    // Close dropdowns on window resize
    window.addEventListener('resize', function() {
        categoryMenuItems.forEach(function(item) {
            item.classList.remove('active');
        });
    });
});

// Cleanup: removed temporary debug marker
