# Importing into Matrix

Importing into a Matrix field is like many other [Field Types](../content-mapping/field-types.md), however, this reference provides a practical example of how to structure your feed properly.

In this example, we'll be importing 2 Entries, which have a single Matrix field called Content Builder. The entry itself has a Featured Image `(Assets)` and Description `(Rich Text)` field. Our Page Builder Matrix field has 3 Blocktypes:

- **Heading**
    - Size `(Dropdown field)`
    - Heading `(Plain Text field)`
- **Copy**
    - Copy `(Rich Text field)`
- **Images**
    - Image `(Assets field)`

### Example Feed Data

The below data is what we'll use for this guide:

::: code
```xml
<?xml version="1.0" encoding="UTF-8"?>
<entries>
    <entry>
        <Title>Guide first entry example</Title>
        <FeaturedImage>ocean_sunset.jpg</FeaturedImage>
        <Description>Lorem ipsum dolor sit amet, consectetur adipiscing elit</Description>

        <Matrix>
            <MatrixBlock>
                <HeadingSize>h1</HeadingSize>
                <HeadingText>This is an H1 tag</HeadingText>
            </MatrixBlock>

            <MatrixBlock>
                <Copy><![CDATA[<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lectus nisl, mattis et luctus ut, varius vitae augue. Integer non lacinia urna, nec molestie enim. Aenean ultricies mattis ligula vel consectetur. Etiam ultrices fringilla lectus nec mollis.</p> <p>Nunc elit magna, semper ac faucibus ut, volutpat eu augue. Vivamus id nibh facilisis, fermentum massa vitae, rhoncus mi. Praesent sit amet efficitur dui.</p>]]></Copy>
            </MatrixBlock>

            <MatrixBlock>
                <HeadingSize>h2</HeadingSize>
                <HeadingText>This is an H2 tag</HeadingText>
            </MatrixBlock>

            <MatrixBlock>
                <Images>
                    <Image>img_fjords.jpg</Image>
                    <Image>recent-images-11.jpg</Image>
                </Images>
            </MatrixBlock>

            <MatrixBlock>
                <Copy><![CDATA[<p>Etiam lectus nisl, mattis et luctus ut, varius vitae augue. Integer non lacinia urna, nec molestie enim. Aenean ultricies mattis ligula vel consectetur. Etiam ultrices fringilla lectus nec mollis.</p> <p>Nunc elit magna, semper ac faucibus ut, volutpat eu augue. Vivamus id nibh facilisis, fermentum massa vitae, rhoncus mi. Praesent sit amet efficitur dui.</p>]]></Copy>
            </MatrixBlock>
        </Matrix>
    </entry>

    <entry>
        <Title>Guide second entry example</Title>
        <FeaturedImage>ocean_sunset.jpg</FeaturedImage>
        <Description>Lorem ipsum dolor sit amet, consectetur adipiscing elit</Description>

        <Matrix>
            <MatrixBlock>
                <HeadingSize>h3</HeadingSize>
                <HeadingText>This is an H3 tag</HeadingText>
            </MatrixBlock>

            <MatrixBlock>
                <Images>
                    <Image>recent-images-11.jpg</Image>
                </Images>
            </MatrixBlock>

            <MatrixBlock>
                <Copy><![CDATA[<p>Integer non lacinia urna, nec molestie enim. Aenean ultricies mattis ligula vel consectetur. Etiam ultrices fringilla lectus nec mollis.</p> <p>Nunc elit magna, semper ac faucibus ut, volutpat eu augue. Vivamus id nibh facilisis, fermentum massa vitae, rhoncus mi. Praesent sit amet efficitur dui.</p>]]></Copy>
            </MatrixBlock>

            <MatrixBlock>
                <HeadingSize>h3</HeadingSize>
                <HeadingText>This is an H3 tag</HeadingText>
            </MatrixBlock>

            <MatrixBlock>
                <Copy><![CDATA[<p>Aenean ultricies mattis ligula vel consectetur. Etiam ultrices fringilla lectus nec mollis.</p> <p>Nunc elit magna, semper ac faucibus ut, volutpat eu augue. Vivamus id nibh facilisis, fermentum massa vitae, rhoncus mi. Praesent sit amet efficitur dui.</p>]]></Copy>
            </MatrixBlock>

            <MatrixBlock>
                <Images>
                    <Image>img_fjords.jpg</Image>
                </Images>
            </MatrixBlock>
        </Matrix>
    </entry>
</entries>
```

```json
{
    "entry": [
        {
            "Title": "Guide first entry example",
            "FeaturedImage": "ocean_sunset.jpg",
            "Description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
            "Matrix": {
                "MatrixBlock": [
                    {
                        "HeadingSize": "h1",
                        "HeadingText": "This is an H1 tag"
                    },
                    {
                        "Copy": {}
                    },
                    {
                        "HeadingSize": "h2",
                        "HeadingText": "This is an H2 tag"
                    },
                    {
                        "Images": {
                            "Image": [
                                "img_fjords.jpg",
                                "recent-images-11.jpg"
                            ]
                        }
                    },
                    {
                        "Copy": {}
                    }
                ]
            }
        },
        {
            "Title": "Guide second entry example",
            "FeaturedImage": "ocean_sunset.jpg",
            "Description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit",
            "Matrix": {
                "MatrixBlock": [
                    {
                        "HeadingSize": "h3",
                        "HeadingText": "This is an H3 tag"
                    },
                    {
                        "Images": {
                            "Image": "recent-images-11.jpg"
                        }
                    },
                    {
                        "Copy": {}
                    },
                    {
                        "HeadingSize": "h3",
                        "HeadingText": "This is an H3 tag"
                    },
                    {
                        "Copy": {}
                    },
                    {
                        "Images": {
                            "Image": "img_fjords.jpg"
                        }
                    }
                ]
            }
        }
    ]
}
```
:::

### Note on structure

You'll notice we're using `Matrix` and `MatrixBlock` for the nodes that contain our content. You can name these whatever you like, however, it's important that you retain this structure. Most importantly, the inner node (`MatrixBlock`) must be all named the same to help preserve content in the same order as your feed.

For example, you should **not** do:

::: code
```xml
<MatrixContent>
    <RichTextBlock>
        <Copy>Lorem ipsum...</Copy>
        <Caption>Some more text.</Caption>
    </RichTextBlock>

    <ImageBlock>
        <Image>img_fjords.jpg</Image>
    </ImageBlock>
</MatrixContent>
```

```json
{
    "RichTextBlock": {
        "Copy": "Lorem ipsum...",
        "Caption": "Some more text."
    },
    "ImageBlock": {
        "Image": "img_fjords.jpg"
    }
}
```
:::

Instead, use the same named node to surround the content for each block:

::: code
```xml
<MatrixContent>
    <MatrixBlock>
        <Copy>Lorem ipsum...</Copy>
        <Caption>Some more text.</Caption>
    </MatrixBlock>

    <MatrixBlock>
        <Image>img_fjords.jpg</Image>
    </MatrixBlock>
</MatrixContent>
```

```json
{
    "MatrixBlock": [
        {
            "Copy": "Lorem ipsum...",
            "Caption": "Some more text."
        },
        {
            "Image": "img_fjords.jpg"
        }
    ]
}
```
:::

Choose either the XML or JSON (depending on your preference), and save as a file in the root of your public directory. We'll assume its `http://craft.local/matrix-feed.xml`.

## Setup your Feed

With your feed data in place, go to Feed Me's main control panel screen, and add a new feed.

![Feedme Matrix Guide Setup](../screenshots/feedme-matrix-guide-setup.png)

Enter the following details:

- **Name** - Matrix Feed
- **Feed URL** - `http://craft.local/matrix-feed.xml`
- **Feed Type** - _XML or JSON_
- **Primary Element** - `entry`
- **Element Type** - Entries
- **Commerce Product Type** - Pages (or similar)
- **Import Strategy** - `Create new elements`, and `Update existing elements`
- **Passkey** - Leave as generated
- **Backup** - Turn on

Click the _Save & Continue_ button to set up the primary element.

## Primary Element

The primary element can be confusing at first, but its vitally important to ensure Feed Me can hone in on the content in your feed correctly. Refer to [Primary Element →](../feature-tour/primary-element.md) for a detailed explanation.

Enter the following details:

- **Primary Element** - `/entries/entry`
- **Pagination URL** - `No Pagination URL`

Click the _Save & Continue_ button to set up the field mapping.

## Field Mapping

Use the below screenshot as a guide for the data we want to map to our Entry and Matrix fields.

![Feedme Matrix Guide Mapping](../screenshots/feedme-matrix-guide-mapping.png)

Click the _Save & Import_ button to begin importing your content.

## Importing your Content

Wait for the feed processing to finish. Remember, you can always navigate away from this confirmation screen.

![Feedme Matrix Guide Start](../screenshots/feedme-matrix-guide-start.png)

:::tip
If you're having issues, or seeing errors at this point, look at the [Troubleshooting](../troubleshooting.md) section.
:::

You should now have 2 brand new Entries with our Matrix data populated.

![Feedme Matrix Guide Finish1](../screenshots/feedme-matrix-guide-finish1.png)

![Feedme Matrix Guide Finish2](../screenshots/feedme-matrix-guide-finish2.png)
