# Importing Commerce Products

This guide will serve as a real-world example of importing Commerce Products into [Craft Commerce](http://craftcommerce.com). We'll be importing two T-Shirt products into Commerce. This guide specifically deals with **single-variant** products.

:::tip
Looking to import products with multiple Variants? Have a look at the [Importing Commerce Variants](/craft-plugins/feed-me/docs/guides/importing-commerce-variants) guide.
:::

### Example Feed Data

The below data is what we'll use for this guide:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<products>
    <product>
        <title>Printed T-Shirt</title>
        <sku>SHIRT-101</sku>
        <price>15</price>
        <stock>500</stock>
        <length>10</length>
        <width>25</width>
        <height>32</height>
        <weight>500</weight>
    </product>

    <product>
        <title>Plain T-Shirt</title>
        <sku>SHIRT-102</sku>
        <price>19</price>
        <unlimitedStock>1</unlimitedStock>
        <length>9</length>
        <width>28</width>
        <height>30</height>
        <weight>480</weight>
    </product>
</products>
```

#### Things to note

- `sku` is compulsory for each product for any import
- `length`, `width`, `height` and `weight` should all be units according to your Commerce settings
- The first product has limited stock, the second unlimited

Choose either the XML or JSON (depending on your preference), and save as a file in the root of your public directory. We'll assume its `http://craft.dev/products-feed.xml`.

* * *

Let's continue and [Setup your Feed →](/craft-plugins/feed-me/docs/guides/importing-commerce-products/setup-your-feed)