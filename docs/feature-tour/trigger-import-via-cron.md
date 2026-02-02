# Trigger Import via Cron

Feed Me does not have a built-in scheduling tool, but you can trigger feeds by [making an HTTP request](#http), or [via the CLI](#console-command).

Regardless of how you trigger an import, the work is offloaded to Craft's [queue](https://craftcms.com/docs/5.x/system/queue.html).

::: warning
Make sure your [queue configuration](https://craftcms.com/docs/5.x/system/queue.html#queue-runners) is suited for the volume and regularity of work generated from Feed Me.
If you are using the default behavior (with [`runQueueAutomatically`](https://craftcms.com/docs/5.x/reference/config/general.html#runqueueautomatically) enabled), your feeds may not process immediately.
:::

## HTTP

A feed can be triggered any time by making a request to its **Direct Feed URL**.
You can use this URL with a third-party scheduler, as a webhook endpoint (say, in a workflow or automation), or from a cron job on another server.

This URL includes a sensitive, feed-specific `passkey` that protects anonymous clients from triggering an import.

::: tip
If you suspect a passkey is compromised, you can set a new one by visiting the feed’s **Edit** screen and replacing the **Passkey** field of sufficient length and complexity.
:::

## Console command

You can also trigger your feed to process via a console command by passing in a comma-separated list of feed IDs to process.

```bash
# Process the feed with ID 1:
php craft feed-me/feeds/queue 1

# Process feeds with IDs 1, 2, and 3:
php craft feed-me/feeds/queue 1,2,3

# Import the first item returned by feed ID 1:
php craft feed-me/feeds/queue 1 --limit=1

# Import the second item returned by feed ID 1:
php craft feed-me/feeds/queue 1 --limit=1 --offset=1

# Log errors with individual items instead of failing:
php craft feed-me/feeds/queue 1 --continue-on-error
```

::: tip
Imports that must be executed in a specific [sequence](importing-your-content.md#sequencing) should be pushed with a single command.
:::

You can also supply a `--all` parameter to push all feeds into the queue. Note that this parameter will ignore any `--limit` and `--offset` parameters supplied.

```bash
php craft feed-me/feeds/queue --all
```

The `feed-me/feeds/queue` command only _queues_ the import job; to actually run the import, the queue must pick up the job.
You can run all pending jobs via the CLI, as well:

```bash
php craft queue/run
```
