# Importing Assets 

Unlike other elements, Assets are a little bit special in how they are processed. Mostly, this is around clarity on the process of 'creating' an asset, in essense, this is uploading an image into Craft from a remote URL or local path. Both these options are supported in Feed Me.

Not only can you use Asset importing to create/upload image files, you can also use it to update them, changing things like custom fields, Title's or even filenames.

This guide will serve as a real-world example for importing a collection of remote images into Craft.

:::tip
If you've already got your image files locally, and just need them imported into Craft, there's a quicker method that doesn't involve Feed Me! First, copy the image files/folders into your volume's root folder. Then go to Utilities > Asset Indexes in your control panel. Select the volume you've just added your files to and hit "Update asset indexes".

This will scan the folder and add and images into Craft as assets.
:::

### Example Feed Data
The below data is what we'll use for this guide:

+++xmltojson
<?xml version="1.0" encoding="UTF-8"?>
<Images>
    <Image>
        <Title>Feed Me Social Card</Title>
        <URL>https://verbb.io/uploads/plugins/feed-me/_800x455_crop_center-center_none/feed-me-social-card.png</URL>
        <Caption>Some Caption</Caption>
    </Image>

    <Image>
        <Title>Super Table Social Card</Title>
        <URL>https://verbb.io/uploads/plugins/super-table/_800x455_crop_center-center_none/super-table-social-card.png</URL>
        <Caption>Another Caption</Caption>
    </Image>
</Images>
+++

Choose either the XML or JSON (depending on your preference), and save as a file in the root of your public directory. We'll assume its `http://craft.local/assets-feed.xml`.

* * *

Let's continue and [Setup your Feed →](docs:guides/importing-assets/setup-your-feed)
