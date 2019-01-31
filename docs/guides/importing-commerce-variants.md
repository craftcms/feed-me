# Importing Commerce Variants

This guide will serve as a real-world example of importing Commerce Products into [Craft Commerce](http://craftcommerce.com). We'll be importing two T-Shirt products into Commerce, each with their own set of variants. This guide specifically deals with **multiple-variant** products.

:::tip
Looking to import products without Variants? Have a look at the [Importing Commerce Products](docs:guides/importing-commerce-products) guide.
:::

### Example Feed Data

The below data is what we'll use for this guide:

+++xmltojson
<?xml version="1.0" encoding="UTF-8"?>
<products>
    <product>
        <title>Printed T-Shirt</title>
        <featuredImage>t-shirt-printed.jpg</featuredImage>
        <description>The best T-Shirt you'll ever wear</description>

        <variants>
            <variant>
                <Title>Red</Title>
                <sku>SHIRT-101-RED</sku>
                <price>15</price>
                <stock>500</stock>
                <length>10</length>
                <width>25</width>
                <height>32</height>
                <weight>500</weight>

                <images>
                    <image>t-shirt-printed-red.jpg</image>
                </images>
            </variant>

            <variant>
                <Title>Blue</Title>
                <sku>SHIRT-101-BLUE</sku>
                <price>15</price>
                <stock>1000</stock>
                <length>10</length>
                <width>25</width>
                <height>32</height>
                <weight>500</weight>
                
                <images>
                    <image>t-shirt-printed-blue.jpg</image>
                </images>
            </variant>
        </variants>
    </product>

    <product>
        <title>Plain T-Shirt</title>
        <featuredImage>t-shirt-plain.jpg</featuredImage>
        <description>The second-best T-Shirt you'll ever wear</description>

        <variants>
            <variant>
                <Title>Green</Title>
                <sku>SHIRT-201-GREEN</sku>
                <price>15</price>
                <stock>500</stock>
                <length>10</length>
                <width>25</width>
                <height>32</height>
                <weight>500</weight>
                
                <images>
                    <image>t-shirt-plain-green.jpg</image>
                </images>
            </variant>

            <variant>
                <Title>Purple</Title>
                <sku>SHIRT-201-PURPLE</sku>
                <price>15</price>
                <stock>1000</stock>
                <length>10</length>
                <width>25</width>
                <height>32</height>
                <weight>500</weight>
                
                <images>
                    <image>t-shirt-plain-purple.jpg</image>
                </images>
            </variant>
        </variants>
    </product>
</products>
+++

#### Things to note

- We've got multiple products, and multiple variants. Product information should be stored under each variant.
- `sku` is compulsory for each variant
- `length`, `width`, `height` and `weight` should all be units according to your Commerce settings
- We have a `title` for the product, and `title`'s for each variant

Choose either the XML or JSON (depending on your preference), and save as a file in the root of your public directory. We'll assume its `http://craft.local/variants-feed.xml`.

* * *

Let's continue and [Setup your Feed →](docs:guides/importing-commerce-variants/setup-your-feed)