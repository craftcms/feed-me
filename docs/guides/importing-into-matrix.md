# Importing into Matrix

Importing into a Matrix field is like many other [Field Types](docs:content-mapping/field-types), however, this reference provides a practical example of how to structure your feed properly.

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

+++xmltojson
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
+++

### Note on structure

You'll notice we're using `Matrix` and `MatrixBlock` for the nodes that contain our content. You can name these whatever you like, however, it's important that you retain this structure. Most importantly, the inner node (`MatrixBlock`) must be all named the same to help preserve content in the same order as your feed.

For example, you should **not** do:

+++xmltojson
<MatrixContent>
    <RichTextBlock>
        <Copy>Lorem ipsum...</Copy>
        <Caption>Some more text.</Caption>
    </RichTextBlock>

    <ImageBlock>
        <Image>img_fjords.jpg</Image>
    </ImageBlock>
</MatrixContent>
+++

Instead, use the same named node to surround the content for each block:

+++xmltojson
<MatrixContent>
    <MatrixBlock>
        <Copy>Lorem ipsum...</Copy>
        <Caption>Some more text.</Caption>
    </MatrixBlock>

    <MatrixBlock>
        <Image>img_fjords.jpg</Image>
    </MatrixBlock>
</MatrixContent>
+++

Choose either the XML or JSON (depending on your preference), and save as a file in the root of your public directory. We'll assume its `http://craft.dev/matrix-feed.xml`.

* * *

Let's continue and [Setup your Feed →](docs:guides/importing-into-matrix/setup-your-feed)