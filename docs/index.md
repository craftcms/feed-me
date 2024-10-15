# About Feed Me

::: warning
You are viewing documentation for Feed Me 4.x and 5.x. [Click here](https://docs.craftcms.com/feed-me/v6) to view the latest version (6.x, compatible with Craft 5.x). Most concepts are portable between versions, but only the latest documentation is receiving updates.
:::

Feed Me is a Craft plugin for super-simple importing of content, either once-off or at regular intervals. With support for XML, RSS, ATOM, CSV or JSON feeds, you'll be able to import your content as Entries, Categories, Craft Commerce Products (and variants), and more.

## Features

- Import data from XML, RSS, ATOM, CSV or JSON feeds, local or remote.
- Built-in importers for [several element types](content-mapping/element-types.md), plus an importer API. 
- Feeds are saved to allow easy re-processing on-demand, or to be used in a Cron job.
- Simple field-mapping interface to match your feed data with your element fields.
- Duplication handling - control what happens when feeds are processed again.
- Uses Craft's Queue service to process feeds in the background.
- Database backups before each feed processing.
- Troubleshoot feed processing issues with logs.
- Grab feed data directly from your twig templates.
