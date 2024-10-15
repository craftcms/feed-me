import {defineConfig} from 'vitepress';
import markdownDefList from 'markdown-it-deflist';

import sidebarContent from './sidebar.json';
import headContent from './head.js';

import {renderInlineCode} from './utils.js';

// https://vitepress.dev/reference/site-config
export default defineConfig({
  // App
  base: '/feed-me/v4/',
  ignoreDeadLinks: true,

  // Documents
  head: headContent,
  title: 'Feed Me 4.x',
  description: 'Documentation for the official Craft CMS Feed Me plugin',

  // Theme
  // https://vitepress.dev/reference/default-theme-config
  themeConfig: {
    logo: '/logo.svg',
    search: {
      provider: 'local',
    },
    nav: [
      {
        text: 'More',
        items: [
          {text: 'Documentation', link: 'https://craftcms.com/docs/4.x'},
          {text: 'Knowledge Base', link: 'https://craftcms.com/knowledge-base'},
          {
            text: 'Craft Class Reference',
            link: 'https://docs.craftcms.com/api/v4',
          },
          {text: 'Craftnet API', link: 'https://docs.api.craftcms.com/'},
        ],
      },
    ],
    socialLinks: [
      {icon: 'github', link: 'https://github.com/craftcms/feed-me'},
    ],
    sidebar: sidebarContent,
  },

  // Output Filtering
  markdown: {
    config(md) {
      // Add HTML5 definition list syntax support:
      md.use(markdownDefList);

      // Disable interpolation for inline code:
      md.renderer.rules.code_inline = renderInlineCode;
    },
  },

  // Hooks
  async transformPageData(pageData, {siteConfig}) {
    // console.log(`Handling: ${pageData.title} (${pageData.relativePath})`);
  },

  // Sitemap
  sitemap: {
    hostname: 'https://docs.craftcms.com/feed-me/v4',
  },

  // Builds
  buildConcurrency: 2,
});
