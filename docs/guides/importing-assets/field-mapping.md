# Field Mapping

Use the below screenshot as a guide for the data we want to map to our asset fields. Items to note are:

- We're providing the full URL to the image to be uploaded as an asset.
- We're also telling Feed Me that if it finds an asset with the same filename to use that, rather than upload a potential duplicate.
- We're also providing the same URL as the filename. Don't worry - Feed Me will determine the filename automatically from the URL. This could optionally be your own specific filename if desired.
- We're checking against existing assets using their filename.

:::tip
The URL or Path field will only be shown if your feed setting are `Create new elements`.
:::

:::tip
While this example is for adding remote URLs, local path's work fine too. Just provide the full path, including the filename of the image you want to add. The path should be relative to the web root of your project.

For example, `to-upload/feed-me.png` would be a folder in your web root named `to-upload` with the file `feed-me.png` inside it.
:::

![Feedme Guide Mapping](../../screenshots/feedme-guide-asset-field-mapping.png)

* * *

Click the _Save & Import_ button to start [Importing your Content →](docs:guides/importing-assets/importing-your-content)
