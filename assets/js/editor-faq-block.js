(function (blocks, element, blockEditor, components, i18n) {
    var el = element.createElement;
    var Fragment = element.Fragment;
    var __ = i18n.__;
    var registerBlockType = blocks.registerBlockType;
    var RichText = blockEditor.RichText;
    var InspectorControls = blockEditor.InspectorControls;
    var useBlockProps = blockEditor.useBlockProps;
    var Button = components.Button;
    var PanelBody = components.PanelBody;
    var ToggleControl = components.ToggleControl;

    var MAX_ITEMS = 6;
    var DEFAULT_ITEMS = [
        { question: '', answer: '' },
        { question: '', answer: '' },
        { question: '', answer: '' }
    ];

    function normalizeItems(items) {
        if (!Array.isArray(items) || !items.length) {
            return DEFAULT_ITEMS.slice();
        }

        return items.slice(0, MAX_ITEMS).map(function (item) {
            return {
                question: item && item.question ? item.question : '',
                answer: item && item.answer ? item.answer : ''
            };
        });
    }

    function updateItem(items, index, key, value) {
        return items.map(function (item, itemIndex) {
            if (itemIndex !== index) {
                return item;
            }

            var nextItem = {
                question: item.question || '',
                answer: item.answer || ''
            };
            nextItem[key] = value;

            return nextItem;
        });
    }

    registerBlockType('thrivingstudio/faq-accordion', {
        apiVersion: 2,
        title: __('FAQ / Related Questions', 'thrivingstudio'),
        description: __('Add a collapsible question-and-answer block near the end of a post.', 'thrivingstudio'),
        icon: 'editor-help',
        category: 'widgets',
        keywords: [
            __('FAQ', 'thrivingstudio'),
            __('Questions', 'thrivingstudio'),
            __('Accordion', 'thrivingstudio')
        ],
        attributes: {
            heading: {
                type: 'string',
                default: __('Frequently Asked Questions', 'thrivingstudio')
            },
            items: {
                type: 'array',
                default: DEFAULT_ITEMS
            },
            firstOpen: {
                type: 'boolean',
                default: false
            }
        },
        supports: {
            anchor: true,
            html: false,
            reusable: true
        },

        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var items = normalizeItems(attributes.items);
            var blockProps = useBlockProps({
                className: 'ts-faq-block ts-faq-block-editor not-prose'
            });

            function setItems(nextItems) {
                setAttributes({ items: normalizeItems(nextItems) });
            }

            return el(
                Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        {
                            title: __('FAQ behavior', 'thrivingstudio'),
                            initialOpen: true
                        },
                        el(ToggleControl, {
                            label: __('Open first question by default', 'thrivingstudio'),
                            checked: !!attributes.firstOpen,
                            onChange: function (value) {
                                setAttributes({ firstOpen: value });
                            }
                        }),
                        el(
                            'p',
                            { className: 'ts-faq-editor-note' },
                            __('Add up to six question slots. Empty or incomplete rows stay hidden on the published post.', 'thrivingstudio')
                        )
                    )
                ),
                el(
                    'section',
                    blockProps,
                    el(RichText, {
                        tagName: 'h2',
                        className: 'ts-faq-block-title',
                        value: attributes.heading,
                        allowedFormats: [],
                        placeholder: __('Frequently Asked Questions', 'thrivingstudio'),
                        onChange: function (value) {
                            setAttributes({ heading: value });
                        }
                    }),
                    el(
                        'div',
                        { className: 'ts-faq-list' },
                        items.map(function (item, index) {
                            return el(
                                'div',
                                {
                                    className: 'ts-faq-editor-item',
                                    key: index
                                },
                                el(
                                    'div',
                                    { className: 'ts-faq-editor-item-header' },
                                    el(
                                        'span',
                                        { className: 'ts-faq-editor-item-label' },
                                        __('Question', 'thrivingstudio') + ' ' + (index + 1)
                                    ),
                                    items.length > 1 &&
                                        el(
                                            Button,
                                            {
                                                variant: 'tertiary',
                                                isDestructive: true,
                                                onClick: function () {
                                                    setItems(items.filter(function (_item, itemIndex) {
                                                        return itemIndex !== index;
                                                    }));
                                                }
                                            },
                                            __('Remove', 'thrivingstudio')
                                        )
                                ),
                                el(RichText, {
                                    tagName: 'div',
                                    className: 'ts-faq-question-input',
                                    value: item.question,
                                    allowedFormats: [],
                                    placeholder: __('Type a clear question...', 'thrivingstudio'),
                                    onChange: function (value) {
                                        setItems(updateItem(items, index, 'question', value));
                                    }
                                }),
                                el(RichText, {
                                    tagName: 'div',
                                    multiline: 'p',
                                    className: 'ts-faq-answer-input',
                                    value: item.answer,
                                    allowedFormats: ['core/bold', 'core/italic', 'core/link'],
                                    placeholder: __('Write a concise answer. Bold, italic, and links are supported.', 'thrivingstudio'),
                                    onChange: function (value) {
                                        setItems(updateItem(items, index, 'answer', value));
                                    }
                                })
                            );
                        })
                    ),
                    el(
                        'div',
                        { className: 'ts-faq-editor-actions' },
                        el(
                            Button,
                            {
                                variant: 'secondary',
                                disabled: items.length >= MAX_ITEMS,
                                onClick: function () {
                                    setItems(items.concat([{ question: '', answer: '' }]));
                                }
                            },
                            items.length >= MAX_ITEMS
                                ? __('Maximum 6 questions', 'thrivingstudio')
                                : __('Add question', 'thrivingstudio')
                        )
                    )
                )
            );
        },

        save: function () {
            return null;
        }
    });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n);
