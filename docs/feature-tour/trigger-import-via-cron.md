# Trigger Import via Cron

:::tip
Triggering an import via Cron still uses Craft's Queue system, so it won't effect the performance of your site.
:::

Once your feed is configured properly, you can trigger the feed processing directly using a special URL. Find this URL by copying the Direct Feed Link from the [Feed Overview](feed-overview.md) screen. You'll receive a URL similar to:

```
http://your.domain/actions/feed-me/feeds/run-task?direct=1&feedId=1&passkey=FwafY5kg3c
```

#### Parameters

- `direct` (required) - Must be set to 1 or true. Tells Feed Me this is a externally-triggered queue job.
- `feedId` (required) - The ID of the feed you wish to process.
- `passkey` (required) - A unique, generated identifier for this feed. Ensures not just anyone can trigger the import.
- `url` (optional) - If your feed URL changes, you can specify it here. Ensure the structure of the feed matches your field mappings.

#### Setup

To setup this feed to run via cron, use one of the following commands - replacing the URL with the one for your feed. Which command you use will depend on your server capabilities, but `wget` is the most common.

```
/usr/bin/wget -O - -q -t 1 "http://your.domain/actions/feed-me/feeds/run-task?direct=1&feedId=1&passkey=FwafY5kg3c"

curl --silent --compressed "http://your.domain/actions/feed-me/feeds/run-task?direct=1&feedId=1&passkey=FwafY5kg3c"

/usr/bin/lynx -source "http://your.domain/actions/feed-me/feeds/run-task?direct=1&feedId=1&passkey=FwafY5kg3c
```

### Console command

You can also trigger your feed to process via a console command. Use the parameter `id` to pass in a single ID, or a comma-delimeted set of ids. You can also use `limit` and `offset` parameters

```
./craft feed-me/feeds/run --id=1

./craft feed-me/feeds/run --id=1,2,3

./craft feed-me/feeds/run --id=1 --limit=1

./craft feed-me/feeds/run --id=1 --limit=1 --offset=1
```
