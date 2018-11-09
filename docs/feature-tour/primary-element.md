# Primary Element

The primary element can be confusing at first, but its vitally important to ensure Feed Me can hone in on the content in your feed correctly.

Take the following example:

+++xmltojson
<?xml version="1.0" encoding="UTF-8"?>
<rss>
    <channel>
        <item>
            <title>My Title</title>
            <slug>my-title</slug>
        </item>

        <item>
            <title>Another Title</title>
            <slug>another-title</slug>
        </item>
    </channel>
</rss>
+++

Your Primary Element would be `item`. This is the node that's repeatable, and you can usually pick it as it'll be the node thats one level above the content you want to import (`title` and `slug` in this case). In the JSON example, you'll see its a plain array, but the same applies.

As a helper, Feed Me will show you how many elements on each node, which will give you a clue as to which node you want to select as the primary element. As each feed can be vastly different, this step seeks to normalise them for Feed Me to effectively process.

* * *

Click on the _Save & Continue_ button to be taken to the [Field Mapping](docs:feature-tour/field-mapping) screen, or press _Save_ to continue editing this screen.
