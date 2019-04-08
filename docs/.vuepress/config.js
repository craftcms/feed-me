module.exports = {
    theme: 'craftdocs',
    ga: 'UA-39036834-16',
    base: '/feed-me/v4/',
    shouldPrefetch: () => false,
    lang: 'en-US',
    title: 'Feed Me Documentation',
    themeConfig: {
        editLinks: true,
        docsRepo: 'craftcms/feed-me',
        docsDir: 'docs',
        docsBranch: 'craft-3',
        nav: [
            {
                text: 'Craft CMS',
                items: [
                    { text: 'Craft 2 Documentation', link: 'https://docs.craftcms.com/v2/' },
                    { text: 'Craft 3 Documentation', link: 'https://docs.craftcms.com/v3/' },
                    { text: 'Craft 2 Class Reference', link: 'https://docs.craftcms.com/api/v2/' },
                    { text: 'Craft 3 Class Reference', link: 'https://docs.craftcms.com/api/v3/' },
                ]
            },
            {
                text: 'Plugins',
                items: [
                    { text: 'Commerce 1 Documentation', link: 'https://docs.craftcms.com/commerce/v1/' },
                    { text: 'Commerce 2 Documentation', link: 'https://docs.craftcms.com/commerce/v2/' },
                    { text: 'Commerce 2 Class Reference', link: 'https://docs.craftcms.com/commerce/api/v2/' },
                    { text: 'Feed Me Documentation', link: '/' },
                ]
            },
            { text: 'Craftnet API', link: 'https://docs.api.craftcms.com/' },
        ],
        sidebar: [
            {
                title: 'Get Started',
                collapsable: false,
                children: [
                    ['', 'Introduction'],
                    'get-started/installation-setup',
                    'get-started/requirements',
                    'get-started/configuration',
                ]
            },
            {
                title: 'Feature Tour',
                collapsable: false,
                children: [
                    'feature-tour/feed-overview',
                    'feature-tour/creating-your-feed',
                    'feature-tour/primary-element',
                    'feature-tour/field-mapping',
                    'feature-tour/importing-your-content',
                    'feature-tour/trigger-import-via-cron',
                    'feature-tour/using-in-your-templates'
                ]
            },
            {
                title: 'Content Mapping',
                collapsable: false,
                children: [
                    'content-mapping/element-types',
                    'content-mapping/field-types'
                ]
            },
            {
                title: 'Developers',
                collapsable: false,
                children: [
                    'developers/field-types',
                    'developers/element-types',
                    'developers/data-types',
                    'developers/events'
                ]
            },
            {
                title: 'Guides',
                collapsable: false,
                children: [
                    'guides/importing-assets',
                    'guides/importing-assets/setup-your-feed',
                    'guides/importing-assets/primary-element',
                    'guides/importing-assets/field-mapping',
                    'guides/importing-assets/importing-your-content',
                    'guides/importing-entries',
                    'guides/importing-entries/setup-your-feed',
                    'guides/importing-entries/primary-element',
                    'guides/importing-entries/field-mapping',
                    'guides/importing-entries/importing-your-content',
                    'guides/importing-commerce-products',
                    'guides/importing-commerce-products/setup-your-feed',
                    'guides/importing-commerce-products/primary-element',
                    'guides/importing-commerce-products/field-mapping',
                    'guides/importing-commerce-products/importing-your-content',
                    'guides/importing-commerce-variants',
                    'guides/importing-commerce-variants/setup-your-feed',
                    'guides/importing-commerce-variants/primary-element',
                    'guides/importing-commerce-variants/field-mapping',
                    'guides/importing-commerce-variants/importing-your-content',
                    'guides/importing-into-matrix',
                    'guides/importing-into-matrix/setup-your-feed',
                    'guides/importing-into-matrix/primary-element',
                    'guides/importing-into-matrix/field-mapping',
                    'guides/importing-into-matrix/importing-your-content',
                    'guides/migrating-from-expressionengine',
                    'guides/migrating-from-wordpress',
                    'guides/craftquest-course'
                ]
            },
            {
                title: 'Support',
                collapsable: false,
                children: [
                    'support/troubleshooting',
                    'support/troubleshooting/logging',
                    'support/troubleshooting/debugging',
                    'support/get-support'
                ]
            },
        ],
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP',
            xml: 'XML',
            json: 'JSON',
        }
    },
    markdown: {
        anchor: {
            level: [2, 3, 4]
        },
        toc: {
            format(content) {
                return content.replace(/[_`]/g, '')
            }
        },
        config(md) {
            md
                .use(replaceApiLinks)
                .use(require('vuepress-theme-craftdocs/markup'))
                .use(require('markdown-it-deflist'))
        }
    },
}

function replaceApiLinks(md) {
    // code adapted from the markdown-it-replace-link plugin
    md.core.ruler.after(
        'inline',
        'replace-link',
        function (state) {
            state.tokens.forEach(function (blockToken) {
                if (blockToken.type === 'inline' && blockToken.children) {
                    blockToken.children.forEach(function (token, tokenIndex) {
                        if (token.type === 'link_open') {
                            token.attrs.forEach(function (attr) {
                                if (attr[0] === 'href') {
                                    let replace = replaceApiLink(attr[1]);
                                    if (replace) {
                                        attr[1] = replace;
                                        let next = blockToken.children[tokenIndex+1];
                                        if (next.type === 'text') {
                                            next.content = next.content.replace(/^(api|config):/, '');
                                        }
                                    }
                                }
                                return false;
                            });
                        }
                    });
                }
            });
            return false;
        }
    );
}

function replaceApiLink(link) {
    link = decodeURIComponent(link)
    let m = link.match(/^(?:api:)?\\?([\w\\]+)(?:::\$?(\w+)(\(\))?)?(?:#([\w\-]+))?$/)
    if (m) {
        let className = m[1]
        let subject = m[2]
        let isMethod = typeof m[3] !== 'undefined'
        let hash = m[4]

        if (className.match(/^craft\\commerce\\/)) {
            let url = 'https://docs.craftcms.com/commerce/api/v2/'+className.replace(/\\/g, '-').toLowerCase()+'.html'
            if (subject) {
                hash = ''
                if (isMethod) {
                    hash = 'method-'
                } else if (!subject.match(/^EVENT_/)) {
                    hash = 'property-'
                }
                hash += subject.replace(/_/g, '-').toLowerCase()
            }
            return url + (hash ? `#${hash}` : '');
        }

        if (className.match(/^craft\\/) || className.match(/^Craft/)) {
            let url = 'https://docs.craftcms.com/api/v3/'+className.replace(/\\/g, '-').toLowerCase()+'.html'
            if (subject) {
                hash = ''
                if (isMethod) {
                    hash = 'method-'
                }
                hash += subject.replace(/_/g, '-').toLowerCase()
            }
            return url + (hash ? `#${hash}` : '');
        }

        if (className.match(/^yii\\/) || className.match(/^Yii/)) {
            let url = 'https://www.yiiframework.com/doc/api/2.0/'+className.replace(/\\/g, '-').toLowerCase()
            if (subject) {
                hash = (isMethod ? `${subject}()` : `\$${subject}`)+'-detail'
            }
            return url + (hash ? `#${hash}` : '');
        }
    }
}
