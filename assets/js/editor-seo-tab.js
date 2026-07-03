(function (plugins, editPostPlugin, element, components, data, i18n) {
    if (!plugins || !editPostPlugin || !element || !components || !data || !i18n) {
        return;
    }

    var el = element.createElement;
    var Fragment = element.Fragment;
    var useEffect = element.useEffect;
    var useState = element.useState;
    var createPortal = element.createPortal;
    var __ = i18n.__;
    var registerPlugin = plugins.registerPlugin;
    var TextControl = components.TextControl;
    var TextareaControl = components.TextareaControl;
    var SelectControl = components.SelectControl;
    var useSelect = data.useSelect;
    var useDispatch = data.useDispatch;

    if (!useSelect || !useDispatch || !createPortal) {
        return;
    }

    var SEO_TAB_ATTRIBUTE = 'data-thrivingstudio-seo-tab';
    var SEO_TAB_PANEL_ID = 'thrivingstudio-seo-tab-panel';
    var SEO_TAB_READY_CLASS = 'thrivingstudio-seo-tab-ready';
    var SEO_TAB_ACTIVE_CLASS = 'thrivingstudio-seo-tab-active';
    var SEO_META_KEYS = {
        articleSubtitle: '_thrivingstudio_article_subtitle',
        title: '_thrivingstudio_seo_title',
        description: '_thrivingstudio_meta_description',
        focusKeyword: '_thrivingstudio_focus_keyword',
        canonical: '_thrivingstudio_canonical_url',
        robots: '_thrivingstudio_robots_meta',
        socialTitle: '_thrivingstudio_social_title',
        socialDescription: '_thrivingstudio_social_description',
        socialImage: '_thrivingstudio_social_image'
    };
    var ROBOTS_OPTIONS = [
        { label: __('Default: index, follow', 'thrivingstudio'), value: '' },
        { label: __('Index, follow', 'thrivingstudio'), value: 'index,follow' },
        { label: __('Noindex, follow', 'thrivingstudio'), value: 'noindex,follow' },
        { label: __('Index, nofollow', 'thrivingstudio'), value: 'index,nofollow' },
        { label: __('Noindex, nofollow', 'thrivingstudio'), value: 'noindex,nofollow' }
    ];

    function stripHtml(value) {
        var wrapper = document.createElement('div');
        wrapper.innerHTML = value || '';

        return (wrapper.textContent || wrapper.innerText || '').trim();
    }

    function getPlainTitle(title) {
        if (typeof title === 'string') {
            return stripHtml(title);
        }

        if (title && typeof title.raw === 'string') {
            return stripHtml(title.raw);
        }

        if (title && typeof title.rendered === 'string') {
            return stripHtml(title.rendered);
        }

        return '';
    }

    function getPlainPostText(value) {
        if (typeof value === 'string') {
            return stripHtml(value);
        }

        if (value && typeof value.raw === 'string') {
            return stripHtml(value.raw);
        }

        if (value && typeof value.rendered === 'string') {
            return stripHtml(value.rendered);
        }

        return '';
    }

    function getMetaValue(meta, key) {
        return meta && typeof meta[key] === 'string' ? meta[key] : '';
    }

    function getCharacterHelp(value, idealLength) {
        var count = (value || '').trim().length;

        return count + '/' + idealLength;
    }

    function getButtonText(button) {
        return (button && button.textContent ? button.textContent : '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function findSidebarTabs() {
        var candidates = Array.prototype.slice.call(
            document.querySelectorAll('[role="tablist"], .edit-post-sidebar__panel-tabs, .interface-complementary-area__tabs, .components-tab-panel__tabs')
        );

        return candidates.find(function (container) {
            var labels = Array.prototype.slice.call(container.querySelectorAll('button')).map(getButtonText);

            return labels.indexOf('post') !== -1 && labels.indexOf('block') !== -1;
        });
    }

    function getSidebarTabHeader(container) {
        if (!container) {
            return null;
        }

        return container.closest('.edit-post-sidebar__panel-tabs, .interface-complementary-area__tabs, .components-tab-panel__tabs') || container;
    }

    function getSidebarShell(header) {
        return header
            ? header.closest('.interface-complementary-area, .edit-post-sidebar, .interface-complementary-area__fill, .edit-post-sidebar__panel')
            : null;
    }

    function updateSeoTabPanelOffset(shell, header) {
        var shellRect;
        var headerRect;
        var panelTop;

        if (!shell || !header || typeof shell.getBoundingClientRect !== 'function' || typeof header.getBoundingClientRect !== 'function') {
            return;
        }

        shellRect = shell.getBoundingClientRect();
        headerRect = header.getBoundingClientRect();
        panelTop = Math.max(0, Math.round(headerRect.bottom - shellRect.top));
        shell.style.setProperty('--thrivingstudio-seo-tab-panel-top', panelTop + 'px');
    }

    function ensureSeoTabPanelHost() {
        var container = findSidebarTabs();
        var header = getSidebarTabHeader(container);
        var shell = getSidebarShell(header);
        var host = document.getElementById(SEO_TAB_PANEL_ID);

        if (!container || !header || !shell) {
            return host;
        }

        shell.classList.add('thrivingstudio-seo-tab-shell');
        updateSeoTabPanelOffset(shell, header);

        if (!host) {
            host = document.createElement('div');
            host.id = SEO_TAB_PANEL_ID;
            host.className = 'thrivingstudio-seo-tab-panel';
            host.setAttribute('role', 'tabpanel');
            host.setAttribute('aria-label', __('SEO', 'thrivingstudio'));
        }

        if (host.parentElement !== shell) {
            shell.appendChild(host);
        }

        return host;
    }

    function createSeoTabButton(sourceButton) {
        var button = document.createElement('button');

        button.type = 'button';
        button.textContent = __('SEO', 'thrivingstudio');
        button.className = sourceButton.className || 'components-button';
        button.setAttribute(SEO_TAB_ATTRIBUTE, 'true');
        button.setAttribute('role', sourceButton.getAttribute('role') || 'tab');
        button.setAttribute('aria-selected', 'false');

        return button;
    }

    function openDocumentSidebar() {
        var editPostDispatch = data.dispatch('core/edit-post');
        var interfaceDispatch = data.dispatch('core/interface');

        if (editPostDispatch && typeof editPostDispatch.openGeneralSidebar === 'function') {
            editPostDispatch.openGeneralSidebar('edit-post/document');
            return;
        }

        if (interfaceDispatch && typeof interfaceDispatch.enableComplementaryArea === 'function') {
            interfaceDispatch.enableComplementaryArea('core/edit-post', 'edit-post/document');
        }
    }

    function syncSeoTabState() {
        var container = findSidebarTabs();
        var isActive = document.body.classList.contains(SEO_TAB_ACTIVE_CLASS);

        if (!container) {
            return;
        }

        Array.prototype.slice.call(container.querySelectorAll('button')).forEach(function (button) {
            var isSeoButton = button.getAttribute(SEO_TAB_ATTRIBUTE) === 'true';
            var label = getButtonText(button);

            if (isSeoButton) {
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            } else if (isActive && (label === 'post' || label === 'block')) {
                button.classList.remove('is-active');
                button.setAttribute('aria-selected', 'false');
            }
        });

        syncSeoPanelVisibility(isActive);
    }

    function syncSeoPanelVisibility(isActive) {
        var container = findSidebarTabs();
        var header = getSidebarTabHeader(container);
        var shell = getSidebarShell(header);
        var host = ensureSeoTabPanelHost();

        if (!header || !shell || !host) {
            return;
        }

        updateSeoTabPanelOffset(shell, header);
        host.style.display = isActive ? '' : 'none';
    }

    function activateSeoTab(event) {
        if (event) {
            event.preventDefault();
        }

        openDocumentSidebar();
        document.body.classList.add(SEO_TAB_ACTIVE_CLASS);
        window.setTimeout(syncSeoTabState, 50);
    }

    function deactivateSeoTab() {
        document.body.classList.remove(SEO_TAB_ACTIVE_CLASS);
        window.setTimeout(syncSeoTabState, 50);
    }

    function insertSeoTab() {
        var container = findSidebarTabs();
        var buttons;
        var blockButton;
        var seoButton;
        var wrapper;

        if (!container || container.querySelector('[' + SEO_TAB_ATTRIBUTE + '="true"]')) {
            return;
        }

        buttons = Array.prototype.slice.call(container.querySelectorAll('button'));
        blockButton = buttons.find(function (button) {
            return getButtonText(button) === 'block';
        });

        if (!blockButton) {
            return;
        }

        seoButton = createSeoTabButton(blockButton);
        seoButton.addEventListener('click', activateSeoTab);

        if (
            blockButton.parentElement &&
            blockButton.parentElement.parentElement === container &&
            blockButton.parentElement.children.length === 1
        ) {
            wrapper = blockButton.parentElement.cloneNode(false);
            wrapper.appendChild(seoButton);
            blockButton.parentElement.insertAdjacentElement('afterend', wrapper);
        } else {
            blockButton.insertAdjacentElement('afterend', seoButton);
        }

        if (!container.getAttribute('data-thrivingstudio-seo-tabs-bound')) {
            container.setAttribute('data-thrivingstudio-seo-tabs-bound', 'true');
            container.addEventListener(
                'click',
                function (event) {
                    var target = event.target;
                    var button = target && typeof target.closest === 'function' ? target.closest('button') : null;
                    var label = getButtonText(button);

                    if (!button || button.getAttribute(SEO_TAB_ATTRIBUTE) === 'true') {
                        return;
                    }

                    if (label === 'post' || label === 'block') {
                        deactivateSeoTab();
                    }
                },
                true
            );
        }

        document.body.classList.add(SEO_TAB_READY_CLASS);
        ensureSeoTabPanelHost();
        syncSeoTabState();
    }

    function SeoTabBridge() {
        useEffect(function () {
            var observer;
            var interval;
            var sync = function () {
                insertSeoTab();
                syncSeoTabState();
            };

            sync();
            interval = window.setInterval(sync, 800);
            observer = new MutationObserver(sync);
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            return function () {
                window.clearInterval(interval);
                observer.disconnect();
                document.body.classList.remove(SEO_TAB_READY_CLASS);
                document.body.classList.remove(SEO_TAB_ACTIVE_CLASS);
            };
        }, []);

        return null;
    }

    function SeoTabContentPortal() {
        var hostState = useState(null);
        var host = hostState[0];
        var setHost = hostState[1];

        useEffect(function () {
            var observer;
            var sync = function () {
                var nextHost = ensureSeoTabPanelHost();

                if (nextHost) {
                    setHost(nextHost);
                    syncSeoPanelVisibility(document.body.classList.contains(SEO_TAB_ACTIVE_CLASS));
                }
            };

            sync();
            observer = new MutationObserver(sync);
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            return function () {
                observer.disconnect();
            };
        }, []);

        if (!host) {
            return null;
        }

        return createPortal(el(SeoFields), host);
    }

    function SeoFields() {
        var editorState = useSelect(function (select) {
            var editor = select('core/editor');
            var meta = editor.getEditedPostAttribute('meta') || {};
            var permalink = typeof editor.getPermalink === 'function' ? editor.getPermalink() : '';
            var postType = typeof editor.getCurrentPostType === 'function' ? editor.getCurrentPostType() : '';

            return {
                excerpt: getPlainPostText(editor.getEditedPostAttribute('excerpt')),
                meta: meta,
                permalink: permalink,
                postType: postType,
                title: getPlainTitle(editor.getEditedPostAttribute('title'))
            };
        });
        var editorActions = useDispatch('core/editor');
        var meta = editorState.meta;
        var articleSubtitle = getMetaValue(meta, SEO_META_KEYS.articleSubtitle) || editorState.excerpt;
        var seoTitle = getMetaValue(meta, SEO_META_KEYS.title);
        var metaDescription = getMetaValue(meta, SEO_META_KEYS.description);
        var canonicalUrl = getMetaValue(meta, SEO_META_KEYS.canonical);
        var socialTitle = getMetaValue(meta, SEO_META_KEYS.socialTitle);
        var socialDescription = getMetaValue(meta, SEO_META_KEYS.socialDescription);
        var isPost = editorState.postType === 'post';
        var previewTitle = socialTitle || seoTitle || editorState.title || __('Untitled post', 'thrivingstudio');
        var previewDescription = socialDescription || metaDescription || __('Add a meta description to preview the search and social snippet.', 'thrivingstudio');
        var previewUrl = canonicalUrl || editorState.permalink || __('Draft URL', 'thrivingstudio');

        function updateMeta(key, value) {
            var nextMeta = Object.assign({}, meta);
            nextMeta[key] = value;

            if (editorActions && typeof editorActions.editPost === 'function') {
                editorActions.editPost({ meta: nextMeta });
            }
        }

        return el(
            'div',
            { className: 'ts-seo-sidebar' },
            isPost && el('h3', { className: 'ts-seo-section-title' }, __('Article display', 'thrivingstudio')),
            isPost && el(TextareaControl, {
                label: __('Subtitle under title', 'thrivingstudio'),
                value: articleSubtitle,
                help: __('Shows below the H1 on the article page and on blog cards.', 'thrivingstudio'),
                placeholder: __('A simple guide to understanding your emotions, your reactions, and the space between feeling and action.', 'thrivingstudio'),
                rows: 3,
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.articleSubtitle, value);
                }
            }),
            el('h3', { className: 'ts-seo-section-title' }, __('Search preview', 'thrivingstudio')),
            el(
                'div',
                {
                    className: 'ts-seo-preview',
                    'aria-label': __('SEO preview', 'thrivingstudio')
                },
                el('p', { className: 'ts-seo-preview-url' }, previewUrl),
                el('p', { className: 'ts-seo-preview-title' }, previewTitle),
                el('p', { className: 'ts-seo-preview-description' }, previewDescription)
            ),
            el(TextControl, {
                label: __('Meta title (SEO title)', 'thrivingstudio'),
                value: seoTitle,
                help: getCharacterHelp(seoTitle, 60),
                placeholder: editorState.title || __('Search title', 'thrivingstudio'),
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.title, value);
                },
                __next40pxDefaultSize: true
            }),
            el(TextareaControl, {
                label: __('Meta description', 'thrivingstudio'),
                value: metaDescription,
                help: getCharacterHelp(metaDescription, 160),
                placeholder: __('Write the search snippet for this post.', 'thrivingstudio'),
                rows: 4,
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.description, value);
                }
            }),
            el(TextControl, {
                label: __('Focus keyword', 'thrivingstudio'),
                value: getMetaValue(meta, SEO_META_KEYS.focusKeyword),
                help: __('Editorial reference only. It is not output as meta keywords.', 'thrivingstudio'),
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.focusKeyword, value);
                },
                __next40pxDefaultSize: true
            }),
            el(SelectControl, {
                label: __('Indexing', 'thrivingstudio'),
                value: getMetaValue(meta, SEO_META_KEYS.robots),
                options: ROBOTS_OPTIONS,
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.robots, value);
                },
                __next40pxDefaultSize: true
            }),
            el(TextControl, {
                label: __('Canonical URL', 'thrivingstudio'),
                value: canonicalUrl,
                type: 'url',
                placeholder: editorState.permalink || 'https://example.com/post',
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.canonical, value);
                },
                __next40pxDefaultSize: true
            }),
            el('h3', { className: 'ts-seo-section-title' }, __('Social preview', 'thrivingstudio')),
            el(TextControl, {
                label: __('Social title', 'thrivingstudio'),
                value: socialTitle,
                placeholder: seoTitle || editorState.title || __('Social title', 'thrivingstudio'),
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.socialTitle, value);
                },
                __next40pxDefaultSize: true
            }),
            el(TextareaControl, {
                label: __('Social description', 'thrivingstudio'),
                value: socialDescription,
                placeholder: metaDescription || __('Description for shared links.', 'thrivingstudio'),
                rows: 3,
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.socialDescription, value);
                }
            }),
            el(TextControl, {
                label: __('Social image URL', 'thrivingstudio'),
                value: getMetaValue(meta, SEO_META_KEYS.socialImage),
                type: 'url',
                placeholder: 'https://example.com/image.jpg',
                onChange: function (value) {
                    updateMeta(SEO_META_KEYS.socialImage, value);
                },
                __next40pxDefaultSize: true
            })
        );
    }

    registerPlugin('thrivingstudio-seo-tab', {
        render: function () {
            return el(
                Fragment,
                {},
                el(SeoTabBridge),
                el(SeoTabContentPortal)
            );
        }
    });
})(
    window.wp.plugins,
    window.wp.editPost,
    window.wp.element,
    window.wp.components,
    window.wp.data,
    window.wp.i18n
);
