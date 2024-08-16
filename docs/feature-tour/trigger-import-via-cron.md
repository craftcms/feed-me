# Trigger Import via Cron

:::tip
Triggering an import via Cron still uses Craft's [queue system](https://craftcms.com/docs/5.x/system/queue.html).
:::

Once your feed is configured properly, you can trigger the feed processing directly using a special URL. Find this URL by copying the **Direct Feed Link** from the [Feed Overview](feed-overview.md) screen. You'll receive a URL that looks something like this:

```
https://my-project.ddev.site/index.php?action=feed-me/feeds/run-task&direct=1&feedId=1&passkey=FwafY5kg3c
```

#### Parameters

- `direct` (required) - Must be set to 1 or true. Tells Feed Me this is a externally-triggered queue job.
- `feedId` (required) - The ID of the feed you wish to process.
- `passkey` (required) - A unique, generated identifier for this feed. Ensures not just anyone can trigger the import.
- `url` (optional) - If your feed URL changes (or is split/parameterized in some way, like using the current date), you can override it here. Ensure the structure of the feed matches your field mappings.

::: warning
Feed IDs and passkeys may not be the same, across environments. Always refer to the control panel for the complete URL.
:::

#### Setup

To setup this feed to run via cron, use one of the following commands - replacing the URL with the one for your feed. Which command you use will depend on your server capabilities, but `wget` is the most common.

::: code-group
```bash wget
/usr/bin/wget -O - -q -t 1 "https://my-project.ddev.site/index.php?action=feed-me/feeds/run-task&direct=1&feedId=1&passkey=FwafY5kg3c"
```
```bash cURL
curl --silent --compressed "https://my-project.ddev.site/index.php?action=feed-me/feeds/run-task&direct=1&feedId=1&passkey=FwafY5kg3c"
```
:::

### Console command

You can also trigger your feed via Craft’s CLI by passing a comma-separated list of feed IDs:

```bash
php craft feed-me/feeds/queue 1

php craft feed-me/feeds/queue 1,2,3
```

You can also use `limit` and `offset` parameters:

```bash
php craft feed-me/feeds/queue 1 --limit=1

php craft feed-me/feeds/queue 1 --limit=1 --offset=1

php craft feed-me/feeds/queue 1 --continue-on-error
```

Use the `--all` flag to push _all_ your feeds into the queue. This parameter ignores `--limit` and `--offset` settings.

```bash
php craft feed-me/feeds/queue --all
```

::: warning
The `feed-me/feeds/queue` command only _queues_ import jobs. To actually execute those jobs, the queue must be running—by default, the queue is triggered when your site is loaded by a client, or a control panel user is active. Projects with many feeds may benefit from setting up a [daemonized runner](https://craftcms.com/docs/5.x/system/queue.html#daemon), or manually running the queue [on a schedule](https://craftcms.com/docs/5.x/system/queue.html#cron).
:::
