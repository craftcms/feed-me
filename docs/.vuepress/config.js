module.exports = {
    title: 'Feed Me Documentation',
    theme: 'craftdocs',
    plugins: [["@vuepress/google-analytics", { ga: "UA-39036834-16" }]],
    base: '/feed-me/v4/',
    shouldPrefetch: () => false,
    lang: 'en-US',
    themeConfig: {
        editLinks: true,
        docsRepo: 'craftcms/feed-me',
        docsDir: 'docs',
        nav: [
            {
                text: 'Craft CMS',
                items: [
                    { text: 'Craft 2 Documentation', link: 'https://craftcms.com/docs/2.x/' },
                    { text: 'Craft 3 Documentation', link: 'https://craftcms.com/docs/3.x/' },
                    { text: 'Craft 2 Class Reference', link: 'https://docs.craftcms.com/api/v2/' },
                    { text: 'Craft 3 Class Reference', link: 'https://docs.craftcms.com/api/v3/' },
                ]
            },
            {
                text: 'Plugins',
                items: [
                    { text: 'Commerce 1 Documentation', link: 'https://craftcms.com/docs/commerce/1.x/' },
                    { text: 'Commerce 2 Documentation', link: 'https://craftcms.com/docs/commerce/2.x/' },
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
                    'guides/importing-entries',
                    'guides/importing-commerce-products',
                    'guides/importing-commerce-variants',
                    'guides/importing-into-matrix',
                    'guides/migrating-from-expressionengine',
                    'guides/migrating-from-wordpress',
                ]
            },
            {
                title: 'Troubleshooting',
                collapsable: false,
                children: [
                    'troubleshooting',
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
        extendMarkdown(md) {
            require("vuepress-theme-craftdocs/markup")(md);
            md.use(require("markdown-it-deflist"));
        },
    },
}
