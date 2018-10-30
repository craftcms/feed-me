# Importing Entries

Importing Entries is one of the more common tasks for Feed Me, but this same guide applies for any other Element Type you wish to import. This guide will serve as a real-world example for importing real estate property listings.

:::tip
Looking for a Matrix example? Check out [Import into Matrix](/craft-plugins/feed-me/docs/guides/importing-into-matrix).
:::

### Example Feed Data
The below data is what we'll use for this guide:

+++xmltojson
<?xml version="1.0" encoding="UTF-8"?>
<propertyList>
    <listing>
        <agentID>7854</agentID>
        <uniqueID>10056505</uniqueID>
        <authority value="exclusive" />
        <underOffer value="no" />
        <newConstruction>1</newConstruction>
        <price display="yes">1175000</price>

        <listingAgent id="1">
            <name>John Citizen</name>
            <email>agent@mywebsite.com</email>
        </listingAgent>

        <address display="no">
            <street>42 Wallaby Way</street>
            <suburb display="no">Sydney</suburb>
            <state>NSW</state>
            <postcode>2000</postcode>
            <country>AUS</country>
        </address>

        <categories>
            <category>Residential</category>
            <category>House</category>
        </categories>

        <headline>Brand New Property</headline>

        <description><![CDATA[<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lectus nisl, mattis et luctus ut, varius vitae augue. Integer non lacinia urna, nec molestie enim. Aenean ultricies mattis ligula vel consectetur. Etiam ultrices fringilla lectus nec mollis.</p>]]></description>

        <features>
            <bedrooms>3</bedrooms>
            <bathrooms>2</bathrooms>
            <garages>0</garages>
        </features>

        <landDetails>
            <area unit="squareMeter">1004</area>
        </landDetails>

        <objects>
            <img id="m" modTime="2016-08-05-00:35:44" format="jpg" url="https://s-media-cache-ak0.pinimg.com/originals/c9/dd/ce/c9ddce1401d452118a75beeeb461d256.jpg" />
            <img id="a" modTime="2016-08-05-00:35:44" format="jpg" url="http://1.bp.blogspot.com/-6lmtHQFj5ZU/U-vLS9J9QrI/AAAAAAAAfxY/WRMOT1Fbv5I/s1600/Rustic_Beach_House_by_SAOTA_on_world_of_architecture_03.jpg" />
        </objects>
    </listing>

    <listing>
        <agentID>7854</agentID>
        <uniqueID>10056506</uniqueID>
        <price display="yes">500000</price>

        <listingAgent id="1">
            <name>John Citizen</name>
            <email>agent@mywebsite.com</email>
        </listingAgent>

        <address display="no">
            <street>43 Wallaby Way</street>
            <suburb display="no">Sydney</suburb>
            <state>NSW</state>
            <postcode>2000</postcode>
            <country>AUS</country>
        </address>

        <categories>
            <category>Commercial</category>
        </categories>

        <headline>Another New Property</headline>

        <description><![CDATA[<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam lectus nisl, mattis et luctus ut, varius vitae augue. Integer non lacinia urna, nec molestie enim. Aenean ultricies mattis ligula vel consectetur. Etiam ultrices fringilla lectus nec mollis.</p>]]></description>

        <features>
            <bedrooms>3</bedrooms>
            <bathrooms>2</bathrooms>
            <garages>0</garages>
        </features>

        <landDetails>
            <area unit="squareMeter">2004</area>
        </landDetails>

        <objects>
            <img id="m" modTime="2016-08-05-00:35:44" format="jpg" url="https://s-media-cache-ak0.pinimg.com/originals/c9/dd/ce/c9ddce1401d452118a75beeeb461d256.jpg" />
        </objects>
    </listing>
</propertyList>
+++

Choose either the XML or JSON (depending on your preference), and save as a file in the root of your public directory. We'll assume its `http://craft.dev/property-feed.xml`.

* * *

Let's continue and [Setup your Feed →](/craft-plugins/feed-me/docs/guides/importing-entries/setup-your-feed)