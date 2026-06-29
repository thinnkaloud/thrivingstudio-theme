(function (richText, element, blockEditor, components, i18n) {
    var el = element.createElement;
    var Fragment = element.Fragment;
    var useState = element.useState;
    var __ = i18n.__;
    var registerFormatType = richText.registerFormatType;
    var applyFormat = richText.applyFormat;
    var insert = richText.insert;
    var removeFormat = richText.removeFormat;
    var useAnchor = richText.useAnchor;
    var RichTextToolbarButton = blockEditor.RichTextToolbarButton;
    var Button = components.Button;
    var Popover = components.Popover;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;

    var FORMAT_NAME = 'thrivingstudio/citation';
    var BASE_REL_VALUES = ['noopener', 'noreferrer'];
    var LINK_TYPE_VALUES = ['sponsored', 'ugc', 'nofollow'];

    function normalizeUrl(url) {
        var trimmedUrl = (url || '').trim();
        var candidateUrl;

        if (!trimmedUrl) {
            return '';
        }

        if (/^(https?:)?\/\//i.test(trimmedUrl)) {
            candidateUrl = trimmedUrl.indexOf('//') === 0 ? 'https:' + trimmedUrl : trimmedUrl;
        } else {
            candidateUrl = 'https://' + trimmedUrl;
        }

        try {
            var parsedUrl = new URL(candidateUrl);

            if (!/^https?:$/.test(parsedUrl.protocol) || !parsedUrl.hostname) {
                return '';
            }

            return parsedUrl.href;
        } catch (error) {
            return '';
        }
    }

    function getSourceLabel(url) {
        var normalizedUrl = normalizeUrl(url);

        if (!normalizedUrl) {
            return '';
        }

        try {
            return new URL(normalizedUrl).hostname.replace(/^www\./i, '');
        } catch (error) {
            return '';
        }
    }

    function getCitationAt(value, index, href) {
        var formats = value && value.formats && value.formats[index] ? value.formats[index] : [];

        return formats.find(function (format) {
            return format.type === FORMAT_NAME &&
                (!href || !format.attributes || format.attributes.href === href);
        });
    }

    function getCitationRange(value, isActive, activeAttributes) {
        var text = value && value.text ? value.text : '';
        var start = typeof value.start === 'number' ? value.start : text.length;
        var end = typeof value.end === 'number' ? value.end : start;
        var href = activeAttributes && activeAttributes.href ? activeAttributes.href : '';

        if (!isActive) {
            return { start: start, end: end };
        }

        var probe = start;

        if (!getCitationAt(value, probe, href) && probe > 0) {
            probe--;
        }

        if (!getCitationAt(value, probe, href)) {
            return { start: start, end: end };
        }

        start = probe;
        while (start > 0 && getCitationAt(value, start - 1, href)) {
            start--;
        }

        end = Math.max(end, probe + 1);
        while (end < text.length && getCitationAt(value, end, href)) {
            end++;
        }

        return { start: start, end: end };
    }

    function getSelectedText(value, range) {
        var text = value && value.text ? value.text : '';
        var start = range && typeof range.start === 'number' ? range.start : 0;
        var end = range && typeof range.end === 'number' ? range.end : start;

        return text.slice(start, end);
    }

    function getRelTokens(rel) {
        return (rel || '').split(/\s+/).filter(Boolean);
    }

    function getLinkTypeFromRel(rel) {
        var tokens = getRelTokens(rel);
        var matchedType = LINK_TYPE_VALUES.find(function (type) {
            return tokens.indexOf(type) !== -1;
        });

        return matchedType || 'follow';
    }

    function buildRel(linkType) {
        var relValues = BASE_REL_VALUES.slice();

        if (LINK_TYPE_VALUES.indexOf(linkType) !== -1) {
            relValues.push(linkType);
        }

        return relValues.join(' ');
    }

    function CitationForm(props) {
        var value = props.value;
        var onChange = props.onChange;
        var onClose = props.onClose;
        var activeAttributes = props.activeAttributes || {};
        var citationRange = getCitationRange(value, props.isActive, activeAttributes);
        var selectedText = getSelectedText(value, citationRange).trim();
        var initialUrl = activeAttributes.href || '';
        var initialLabel = selectedText || getSourceLabel(initialUrl);
        var urlState = useState(initialUrl);
        var url = urlState[0];
        var setUrl = urlState[1];
        var labelState = useState(initialLabel);
        var label = labelState[0];
        var setLabel = labelState[1];
        var linkTypeState = useState(getLinkTypeFromRel(activeAttributes.rel));
        var linkType = linkTypeState[0];
        var setLinkType = linkTypeState[1];
        var errorState = useState('');
        var error = errorState[0];
        var setError = errorState[1];

        function saveCitation(event) {
            event.preventDefault();

            var normalizedUrl = normalizeUrl(url);
            var finalLabel = label.trim() || getSourceLabel(normalizedUrl);

            if (!normalizedUrl || !finalLabel) {
                setError(__('Add a valid source URL and label.', 'thrivingstudio'));
                return;
            }

            var start = citationRange.start;
            var end = citationRange.end;
            var text = value.text || '';
            var prefix = start === end && start > 0 && !/\s/.test(text.charAt(start - 1)) ? ' ' : '';
            var suffix = end < text.length && !/\s/.test(text.charAt(end)) ? ' ' : '';
            var replacement = prefix + finalLabel + suffix;
            var citationStart = start + prefix.length;
            var citationEnd = citationStart + finalLabel.length;
            var nextValue = insert(value, replacement, start, end);

            nextValue = applyFormat(
                nextValue,
                {
                    type: FORMAT_NAME,
                    attributes: {
                        href: normalizedUrl,
                        target: '_blank',
                        rel: buildRel(linkType)
                    }
                },
                citationStart,
                citationEnd
            );

            onChange(nextValue);
            onClose();
        }

        function updateUrl(nextUrl) {
            setUrl(nextUrl);
            setError('');

            if (!label.trim() || label === getSourceLabel(url)) {
                setLabel(getSourceLabel(nextUrl));
            }
        }

        return el(
            'form',
            {
                className: 'ts-citation-popover',
                onSubmit: saveCitation
            },
            el(
                'p',
                { className: 'ts-citation-popover-title' },
                __('Inline citation', 'thrivingstudio')
            ),
            el(TextControl, {
                label: __('Source URL', 'thrivingstudio'),
                value: url,
                placeholder: 'https://example.com/article',
                type: 'text',
                inputMode: 'url',
                onChange: updateUrl,
                autoFocus: true,
                __next40pxDefaultSize: true,
                __nextHasNoMarginBottom: true
            }),
            el(TextControl, {
                label: __('Label', 'thrivingstudio'),
                help: __('Shown inside the citation pill.', 'thrivingstudio'),
                value: label,
                placeholder: 'example.com',
                onChange: function (nextLabel) {
                    setLabel(nextLabel);
                    setError('');
                },
                __next40pxDefaultSize: true,
                __nextHasNoMarginBottom: true
            }),
            el(SelectControl, {
                label: __('Link type', 'thrivingstudio'),
                help: __('Use standard for normal citations. Use sponsored for paid or affiliate links.', 'thrivingstudio'),
                value: linkType,
                options: [
                    {
                        label: __('Standard citation / follow', 'thrivingstudio'),
                        value: 'follow'
                    },
                    {
                        label: __('No endorsement / nofollow', 'thrivingstudio'),
                        value: 'nofollow'
                    },
                    {
                        label: __('Sponsored / affiliate', 'thrivingstudio'),
                        value: 'sponsored'
                    },
                    {
                        label: __('User-generated', 'thrivingstudio'),
                        value: 'ugc'
                    }
                ],
                onChange: function (nextLinkType) {
                    setLinkType(nextLinkType);
                    setError('');
                },
                __next40pxDefaultSize: true,
                __nextHasNoMarginBottom: true
            }),
            error && el(
                'p',
                {
                    className: 'ts-citation-popover-error',
                    role: 'alert'
                },
                error
            ),
            el(
                'div',
                { className: 'ts-citation-popover-actions' },
                props.isActive && el(
                    Button,
                    {
                        variant: 'tertiary',
                        isDestructive: true,
                        onClick: function () {
                            onChange(removeFormat(value, FORMAT_NAME, citationRange.start, citationRange.end));
                            onClose();
                        }
                    },
                    __('Remove', 'thrivingstudio')
                ),
                el(
                    Button,
                    {
                        variant: 'primary',
                        type: 'submit'
                    },
                    props.isActive
                        ? __('Update citation', 'thrivingstudio')
                        : __('Add citation', 'thrivingstudio')
                )
            )
        );
    }

    registerFormatType(FORMAT_NAME, {
        title: __('Citation', 'thrivingstudio'),
        tagName: 'a',
        className: 'ts-inline-citation',
        attributes: {
            href: 'href',
            target: 'target',
            rel: 'rel'
        },
        edit: function (props) {
            var openState = useState(false);
            var isOpen = openState[0];
            var setIsOpen = openState[1];
            var fallbackAnchorState = useState(null);
            var fallbackAnchor = fallbackAnchorState[0];
            var setFallbackAnchor = fallbackAnchorState[1];
            var selectionAnchor = useAnchor({
                editableContentElement: props.contentRef ? props.contentRef.current : null,
                settings: {
                    tagName: 'a',
                    className: 'ts-inline-citation',
                    isActive: props.isActive
                }
            });

            function closePopover() {
                setIsOpen(false);
                setFallbackAnchor(null);

                if (props.onFocus) {
                    props.onFocus();
                }
            }

            function openPopover(event) {
                if (event && event.currentTarget) {
                    setFallbackAnchor(event.currentTarget);
                }

                setIsOpen(true);
            }

            return el(
                Fragment,
                {},
                props.isVisible !== false && el(RichTextToolbarButton, {
                    icon: 'admin-links',
                    title: __('Citation', 'thrivingstudio'),
                    isActive: props.isActive || isOpen,
                    onClick: openPopover,
                    'aria-haspopup': 'dialog',
                    'aria-expanded': isOpen
                }),
                isOpen && el(
                    Popover,
                    {
                        anchor: selectionAnchor || fallbackAnchor,
                        placement: 'bottom-start',
                        onClose: closePopover,
                        focusOnMount: 'firstElement',
                        constrainTabbing: true
                    },
                    el(CitationForm, {
                        value: props.value,
                        onChange: props.onChange,
                        onClose: closePopover,
                        isActive: props.isActive,
                        activeAttributes: props.activeAttributes
                    })
                )
            );
        }
    });
})(
    window.wp.richText,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
