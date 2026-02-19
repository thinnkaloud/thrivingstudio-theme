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

// Quote Cards Slider Logic
(function() {
    const slider = document.getElementById('quote-slider');
    if (!slider) return;

    const prevBtn = document.getElementById('quote-slider-prev');
    const nextBtn = document.getElementById('quote-slider-next');

    // Find the width of one card
    function getCardWidth() {
        const card = slider.querySelector('div.snap-center');
        return card ? card.offsetWidth : slider.clientWidth;
    }

    // Make only the centered card clickable on mobile, all clickable on desktop
    function updatePointerEvents() {
        const cards = slider.querySelectorAll('div.snap-center');
        if (window.innerWidth < 768) { // Mobile: only center card clickable
            let minDist = Infinity;
            let centerCard = null;
            const sliderRect = slider.getBoundingClientRect();
            const sliderCenter = sliderRect.left + sliderRect.width / 2;
            cards.forEach(card => {
                const cardRect = card.getBoundingClientRect();
                const cardCenter = cardRect.left + cardRect.width / 2;
                const dist = Math.abs(cardCenter - sliderCenter);
                if (dist < minDist) {
                    minDist = dist;
                    centerCard = card;
                }
                card.style.pointerEvents = 'none';
                const link = card.querySelector('a');
                if (link) link.style.pointerEvents = 'none';
            });
            if (centerCard) {
                centerCard.style.pointerEvents = 'auto';
                const link = centerCard.querySelector('a');
                if (link) link.style.pointerEvents = 'auto';
            }
            // Add touch/click to previews to scroll them to center
            cards.forEach(card => {
                card.removeEventListener('click', card._scrollToCenterHandler || (() => {}));
                if (card !== centerCard) {
                    const handler = function(e) {
                        e.preventDefault();
                        // Center the card in the slider using scrollTo
                        const cardRect = card.getBoundingClientRect();
                        const sliderRect = slider.getBoundingClientRect();
                        // Card's left relative to slider
                        const cardLeft = card.offsetLeft;
                        const cardWidth = card.offsetWidth;
                        const sliderWidth = slider.clientWidth;
                        // Target scrollLeft to center the card
                        const targetScrollLeft = cardLeft - (sliderWidth / 2) + (cardWidth / 2);
                        slider.scrollTo({ left: targetScrollLeft, behavior: 'smooth' });
                    };
                    card.addEventListener('click', handler);
                    card._scrollToCenterHandler = handler;
                } else {
                    card._scrollToCenterHandler = null;
                }
            });
        } else { // Desktop: all cards clickable
            cards.forEach(card => {
                card.style.pointerEvents = 'auto';
                const link = card.querySelector('a');
                if (link) link.style.pointerEvents = 'auto';
                // Remove mobile-only handler if present
                if (card._scrollToCenterHandler) {
                    card.removeEventListener('click', card._scrollToCenterHandler);
                    card._scrollToCenterHandler = null;
                }
            });
        }
    }

    const scroll = () => {
        const isEnd = slider.scrollWidth - slider.scrollLeft === slider.clientWidth;
        const isStart = slider.scrollLeft === 0;
        if (prevBtn) prevBtn.style.display = isStart ? 'none' : 'flex';
        if (nextBtn) nextBtn.style.display = isEnd ? 'none' : 'flex';
        updatePointerEvents();
    };

    const scrollNext = () => {
        slider.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
    };

    const scrollPrev = () => {
        slider.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
    };

    slider.addEventListener('scroll', scroll);
    if (nextBtn) nextBtn.addEventListener('click', scrollNext);
    if (prevBtn) prevBtn.addEventListener('click', scrollPrev);
    window.addEventListener('load', scroll);
    window.addEventListener('resize', scroll);
    // Initial pointer events setup
    updatePointerEvents();
})();

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