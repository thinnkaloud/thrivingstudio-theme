<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="impact-site-verification" content="e85a892a-537b-4983-a531-0480dc6450ca">
    <?php wp_head(); ?>

    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8401911572659149" crossorigin="anonymous"></script>
    
    <!-- Ahrefs Analytics -->
    <script>
    (function() {
      var script = document.createElement('script');
      script.async = true;
      script.src = 'https://analytics.ahrefs.com/analytics.js';
      script.setAttribute('data-key', 'wvLnIjU24PNUMGOyghpDsQ');
      document.head.appendChild(script);
    })();
    </script>
    
    <!-- Critical CSS for above-the-fold content -->
    <style>
    /* Essential styles for initial render */
    #masthead {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
    }
    </style>
</head>
<body <?php body_class('font-sans bg-white min-h-screen'); ?>>
    <?php wp_body_open(); ?>

    <?php $topbar_message = get_theme_mod('thrivingstudio_topbar_message', ''); ?>
    <?php $topbar_show = get_theme_mod('thrivingstudio_topbar_show', true); ?>
    <?php if (!empty($topbar_message) && $topbar_show) : ?>
        <div class="ts-topbar-message">
            <?php echo esc_html($topbar_message); ?>
        </div>
    <?php endif; ?>

<style>
/* Adjust sticky header position for all cases */
#masthead {
  top: 0; /* Default for non-admin users */
}
body.admin-bar #masthead {
  top: 32px; /* Default for desktop admin users */
}
@media screen and (max-width: 782px) {
  body.admin-bar #masthead {
    top: 46px; /* For mobile admin users */
  }
}

/* Light mode navigation styling */
.primary-menu a,
.primary-menu li a {
  color: #374151 !important; /* gray-700 */
  transition: color 0.2s ease-in-out;
}

.primary-menu a:hover,
.primary-menu li a:hover {
  color: #111827 !important; /* gray-900 */
}

/* Active/Current menu item styling */
.primary-menu .current-menu-item a,
.primary-menu .current_page_item a,
.primary-menu .current-menu-ancestor a,
.primary-menu li.current-menu-item a,
.primary-menu li.current_page_item a {
  color: #000000 !important; /* black color for active state */
  font-weight: 700 !important; /* bold for active state */
}

/* Ad containers removed - using Site Kit for AdSense management */

/* Mobile menu button fixes for better touch interaction */
#mobile-menu-button {
  min-width: 44px !important;
  min-height: 44px !important;
  touch-action: manipulation !important;
  cursor: pointer !important;
  pointer-events: auto !important;
  z-index: 60 !important;
  position: relative !important;
  background: transparent !important;
  border: none !important;
  outline: none !important;
  /* Ensure button is clickable */
  user-select: none !important;
  -webkit-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
}

@media (max-width: 767px) {
  #mobile-menu-button {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 8px !important;
    margin-left: 8px !important;
    width: 44px !important;
    height: 44px !important;
    background-color: transparent !important;
    border: 1px solid transparent !important;
    border-radius: 6px !important;
    transition: all 0.2s ease-in-out !important;
    /* Force visibility and clickability */
    opacity: 1 !important;
    visibility: visible !important;
    pointer-events: auto !important;
    position: relative !important;
    z-index: 999 !important;
  }
  
  #mobile-menu-button:hover {
    background-color: rgba(0, 0, 0, 0.05) !important;
  }
  
  #mobile-menu-button:active {
    background-color: rgba(0, 0, 0, 0.1) !important;
    transform: scale(0.95) !important;
  }
  
  #mobile-menu-button svg {
    width: 24px !important;
    height: 24px !important;
    pointer-events: none !important;
  }
  
  /* Ensure no other elements are blocking the button */
  #mobile-menu-button * {
    pointer-events: none !important;
  }
}
</style>

<header id="masthead" class="site-header sticky z-50 backdrop-blur-sm bg-white/80 border-b border-gray-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:grid md:grid-cols-3 md:items-center">
                <!-- Logo (left) -->
                <div class="flex items-center md:min-w-[160px]">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="text-2xl text-gray-800">
                        <span class="font-bold">Thriving</span><span class="font-normal">Studio</span>
                    </a>
                </div>
                
                <!-- Centered Menu (hidden on mobile) -->
                <div class="hidden md:flex md:items-center justify-around flex-1 max-w-md mx-auto">
                    <?php 
                    if (has_nav_menu('primary')) {
                        get_template_part('template-parts/nav'); 
                    }
                    ?>
                </div>
                <!-- CTA (right on desktop), Hamburger on mobile -->
                <div class="flex items-center md:justify-end w-auto md:min-w-[160px]">
                    <!-- Desktop CTA -->
                    <div class="hidden md:flex items-center space-x-4">
                    <a href="<?php echo esc_url(get_theme_mod('thrivingstudio_header_cta_link', '#')); ?>" class="ts-header-cta inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium w-full text-center">
                        <?php echo esc_html(get_theme_mod('thrivingstudio_header_cta_text', __('Get In Touch', 'thrivingstudio'))); ?>
                    </a>
                    </div>
                    <!-- Mobile Hamburger Button (hidden on desktop) -->
                    <button id="mobile-menu-button" type="button" class="mobile-menu-btn ts-mobile-menu-button inline-flex md:hidden items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 ml-2" aria-controls="mobile-menu" aria-expanded="false">
                        <!-- Icon when menu is closed -->
                        <svg class="js-mobile-menu-open-icon block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Icon when menu is open -->
                        <svg class="js-mobile-menu-close-icon hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state. -->
        <div class="md:hidden hidden bg-white border-t border-gray-200" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <?php 
                // We pass a custom argument 'is_mobile' to our nav menu
                get_template_part('template-parts/nav', null, ['is_mobile' => true]); 
                ?>
            </div>
            <!-- Category Menu for Mobile -->
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="px-5 pb-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Categories</h3>
                </div>
                <?php get_template_part('template-parts/category-menu', null, ['is_mobile' => true]); ?>
            </div>
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-5">
                     <a href="<?php echo esc_url(get_theme_mod('thrivingstudio_header_cta_link', '#')); ?>" class="ts-header-cta w-full text-center inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium w-full text-center">
                        <?php echo esc_html(get_theme_mod('thrivingstudio_header_cta_text', __('Get In Touch', 'thrivingstudio'))); ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <?php if (is_post_type_archive('quote_card')) : ?>
    <style>
    /* Force minimal spacing for quote card archive */
    main.flex-1 {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    main.flex-1 .container {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }
    main.flex-1 .mb-4 {
        margin-bottom: 0 !important;
    }
    main.flex-1 h1 {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    </style>
    <?php endif; ?>

<!-- Desktop Category Menu (hidden on mobile) -->
<div class="hidden md:block">
<?php get_template_part('template-parts/category-menu'); ?>
</div>

<div id="site-wrapper">
    <div id="site-content" class="flex-1">
        <script>
        (function() {
            function onReady(fn) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fn, { once: true });
                } else { fn(); }
            }
            onReady(function() {
                var btn = document.getElementById('mobile-menu-button');
                var menu = document.getElementById('mobile-menu');
                if (!btn || !menu || btn._tsMenuBound) return;
                btn._tsMenuBound = true;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                    var openIcon = document.querySelector('.js-mobile-menu-open-icon');
                    var closeIcon = document.querySelector('.js-mobile-menu-close-icon');
                    if (openIcon && closeIcon) {
                        openIcon.classList.toggle('hidden');
                        closeIcon.classList.toggle('hidden');
                    }
                    var isExpanded = btn.getAttribute('aria-expanded') === 'true';
                    btn.setAttribute('aria-expanded', String(!isExpanded));
                }, { passive: false });
            });
        })();
        </script>
