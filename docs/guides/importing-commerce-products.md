# Importing Commerce Products

This guide will serve as a real-world example of importing Commerce Products into [Craft Commerce](http://craftcommerce.com). We'll be importing two T-Shirt products into Commerce. This guide specifically deals with **single-variant** products.

:::tip
Looking to import products with multiple Variants? Have a look at the [Importing Commerce Variants](importing-commerce-variants.md) guide.
:::

### Example Feed Data

The below data is what we'll use for this guide:

::: code
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

```json
{
    "product": [
        {
            "title": "Printed T-Shirt",
            "sku": "SHIRT-101",
            "price": "15",
            "stock": "500",
            "length": "10",
            "width": "25",
            "height": "32",
            "weight": "500"
        },
        {
            "title": "Plain T-Shirt",
            "sku": "SHIRT-102",
            "price": "19",
            "unlimitedStock": "1",
            "length": "9",
            "width": "28",
            "height": "30",
            "weight": "480"
        }
    ]
}
```
:::

#### Things to note

- `sku` is compulsory for each product for any import
- `length`, `width`, `height` and `weight` should all be units according to your Commerce settings
- The first product has limited stock, the second unlimited

Choose either the XML or JSON (depending on your preference), and save as a file in the root of your public directory. We'll assume its `http://craft.local/products-feed.xml`.

## Setup your Feed

With your feed data in place, go to Feed Me's main control panel screen, and add a new feed.

![Feedme Matrix Guide Setup](../screenshots/feedme-matrix-guide-setup.png)

Enter the following details:

- **Name** - Products
- **Feed URL** - `http://craft.local/products-feed.xml`
- **Feed Type** - _XML or JSON_
- **Element Type** - Products
- **Commerce Product Type** - Clothing (or similar)
- **Import Strategy** - `Create new elements`, and `Update existing elements`
- **Passkey** - Leave as generated
- **Backup** - Turn on

Click the _Save & Continue_ button to set up the primary element.

## Primary Element

The primary element can be confusing at first, but its vitally important to ensure Feed Me can hone in on the content in your feed correctly. Refer to [Primary Element →](../feature-tour/primary-element.md) for a detailed explanation.

Enter the following details:

- **Primary Element** - `/products/product`
- **Pagination URL** - `No Pagination URL`

Click the _Save & Continue_ button to set up the field mapping.

## Field Mapping

Use the below screenshot as a guide for the data we want to map to our product fields.

![Feedme Products Guide Mapping](../screenshots/feedme-products-guide-mapping.png)

#### Things to note

- As these are single variant products, we check the `Is Default` option. This tells Commerce this variant is the default variant for this product.
- Our unique identifier is the Variant SKU - simply as its unique to each product.
- We have no custom fields for Products setup - but they would appear underneath the Product Variant Fields as per a regular [Importing into Entries](importing-entries.md) workflow.

Click the _Save & Import_ button to begin importing your content.

## Importing your Content

Wait for the feed processing to finish. Remember, you can always navigate away from this confirmation screen.

![Feedme Matrix Guide Start](../screenshots/feedme-matrix-guide-start.png)

:::tip
If you're having issues, or seeing errors at this point, look at the [Troubleshooting](../troubleshooting.md) section.
:::

You should now have 2 brand new products in your Clothing product type.

![Feedme Matrix Guide Finish1](../screenshots/feedme-matrix-guide-finish1.png)

![Feedme Matrix Guide Finish2](../screenshots/feedme-matrix-guide-finish2.png)


