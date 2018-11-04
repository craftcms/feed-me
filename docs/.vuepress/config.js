// ----------------------------------------
// Plugin Variables
// ----------------------------------------

let title = 'Feed Me';
let handle = 'feed-me';
let color = 'e64c4c';
let base = '/craft-plugins/feed-me/docs/';

let docsBranch = 'craft-3';

let versions = [
    {
        pluginVersion: 3,
        craftVersion: 3,
    },
    {
        pluginVersion: 2,
        craftVersion: 2,
    }
];

module.exports = {
    theme: 'verbbdocs',
    ga: 'UA-53491015-36',
    base: base,
    shouldPrefetch: () => false,
    title: title,
    head: [
        ['link', { rel: 'icon', href: 'https://verbb.io/actions/businessLogic/generateFavicon?color=' + color }],
        ['link', { rel: 'stylesheet', href: 'https://fast.fonts.net/cssapi/ad5fdbdd-7653-4ef4-bc97-778d849b0a33.css' }],
    ],
    themeConfig: {
        logo: '/logo.svg',
        handle: handle,
        docsRepo: 'verbb/' + handle,
        docsDir: 'docs',
        docsBranch: docsBranch,
        editLinks: false,
        sidebarDepth: 0,
        algolia: {
            apiKey: '25355bee5b931c1128e24882ea7cf389',
            indexName: handle,
        },
        versions: versions,
        sidebar: {
            '/': [
                {
                    title: 'Get Started',
                    children: [
                        'get-started/installation-setup',
                        'get-started/requirements',
                        'get-started/configuration',
                    ]
                },
                {
                    title: 'Feature Tour',
                    children: [
                        'feature-tour/feed-overview',
                        'feature-tour/creating-your-feed',
                        'feature-tour/field-mapping',
                        'feature-tour/importing-your-content',
                        'feature-tour/trigger-import-via-cron',
                        'feature-tour/using-in-your-templates',
                    ]
                },
                {
                    title: 'Content Mapping',
                    children: [
                        'content-mapping/element-types',
                        {
                            children: [
                                'content-mapping/element-types/assets',
                                'content-mapping/element-types/categories',
                                'content-mapping/element-types/craft-commerce-products',
                                'content-mapping/element-types/entries',
                                'content-mapping/element-types/users',
                            ],
                        },
                        'content-mapping/field-types',
                    ]
                },
                {
                    title: 'Developers',
                    children: [
                        'developers/events-reference',
                        'developers/hooks-reference',
                        'developers/field-types',
                        'developers/element-types',
                        'developers/data-types',
                    ]
                },
                {
                    title: 'Guides',
                    children: [
                        'guides/importing-entries',
                        {
                            children: [
                                'guides/importing-entries/setup-your-feed',
                                'guides/importing-entries/field-mapping',
                                'guides/importing-entries/importing-your-content',
                            ]
                        },
                        'guides/importing-commerce-products',
                        {
                            children: [
                                'guides/importing-commerce-products/setup-your-feed',
                                'guides/importing-commerce-products/field-mapping',
                                'guides/importing-commerce-products/importing-your-content',
                            ]
                        },
                        'guides/importing-commerce-variants',
                        {
                            children: [
                                'guides/importing-commerce-variants/setup-your-feed',
                                'guides/importing-commerce-variants/field-mapping',
                                'guides/importing-commerce-variants/importing-your-content',
                            ]
                        },
                        'guides/importing-into-matrix',
                        {
                            children: [
                                'guides/importing-into-matrix/setup-your-feed',
                                'guides/importing-into-matrix/field-mapping',
                                'guides/importing-into-matrix/importing-your-content',
                            ]
                        },
                        'guides/migrating-from-expressionengine',
                        'guides/migrating-from-wordpress',
                        'guides/updating-from-1-x-x'
                    ]
                },
                {
                    title: 'Support',
                    children: [
                        'support/troubleshooting',
                        {
                            children: [
                                'support/troubleshooting/logging',
                                'support/troubleshooting/debugging',
                            ]
                        },
                        'support/get-support',
                        'support/faqs'
                    ]
                },
            ],
        },
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP',
            css: 'CSS',
            scss: 'SCSS',
            js: 'JS',
            html: 'HTML',
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
                .use(require('vuepress-theme-verbbdocs/markup'))
                .use(require('markdown-it-deflist'))
        }
    },
}
