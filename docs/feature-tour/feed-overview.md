# Feed Overview

Go to the main Feed Me section via your CP sidebar menu. You'll be presented with a table listing of your saved feeds, or none if you haven't set any up yet.

This overview shows the following:

- **Name** — Name your feed something useful so you'll remember what it does.
- **Feed URL** — The URL (or filesystem path) to your feed.
- **Type** — The data type you're importing.
- **Element Type** — The element type you are importing into.
    - Depending on the element type chosen. Entries will show **Section/Entry Type**, Categories will show **Group**, etc.
- **Target Site** — Which site content should be imported into. Each import targets a single site.
- **Strategy** — One or more descriptos of how Feed Me reconciles entries in initial and subsequent runs. See [import strategy](creating-your-feed.md#import-strategy) for more information.
- **Process** — Quickly queue a background import job, without adjusting settings.

The final column displays a few actions:

- **Settings** (icon) — [See below](#settings-pane).
- **Move** (icon) — Drag and drop the row to organize your feeds.
- **Delete (icon)** — Delete the feed.

### Settings Pane

Clicking on the settings “cog” icon will expand additional settings for a feed:

- **Debug** — Opens in a new window and runs the [Debug action](../troubleshooting.md#debugging).
- **Feed Status** — Takes you to an overview screen showing the process of your feed (if it’s currently running).
- **Duplicate Feed** — Duplicates the feed into a new feed, preserving all its settings.
- **Direct Feed URL** — Can be used in your [Cron job setup](trigger-import-via-cron.md) to directly trigger the job.

### Creating and Editing Feeds

[Create a new feed](creating-your-feed.md) by pressing the red **+ New Feed** button. To edit an existing feed’s settings, click its name in the first column.
