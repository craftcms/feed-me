# Updating from 1.x.x

Feed Me 2.0 is a major step forward in terms of functionality, and is close to a full rewrite from the 1.x.x version.

As with many major updates, this brings about breaking changes. While Feed Me will automatically do its best to update your feeds to be compatible with 2.0, there are some areas that cannot be migrated. Its also just a good idea to double-check your feed settings and mapping after the update.

**We recommend taking a screenshot of your v1 feed mapping to double-check against, before updating to 2.0.**

## Elements

As support for more than just entries has been added, you'll now need to actively choose which element type you want to import your content into. Fortunately, this is done automatically through the plugin migration, but double-check to ensure your section and entry type settings are correct.

## Field Mapping

With the addition of attribute-mapping support, and a re-write of the XML parsing logic - its a good idea to check your fields are mapped correctly. Again, the migration will do its best to update settings automatically, but its strongly encouraged to double-check this.

### Breaking Change 1

Feed Me 1.x.x read XML feed nodes in a case-insensitive way. Feed Me 2.0 now takes character case into consideration when mapping your feed. For example:

```
// Feed Me 1.x.x read the below as "mycontentnode"
<MyContentNode>Some value</MyContentNode>

// Feed Me 2.0 reads the below as "MyContentNode"
<MyContentNode>Some value</MyContentNode>
```

As such, you'll need to **manually re-map these fields**, as there is no feasible way to automate this change between versions.

### Breaking Change 2

Feed Me 2.0 now support attributes for XML-based feeds. This inclusion has meant any node with attributes will need to be **manually re-mapped**, even if you aren't using the attributes. This is because when an attribute is present for a node, the text and attributes are stored separately, so you can select which you want.

```
<item>
    <node title="My Title">My Value</node>
</item>

// Feed Me 1.x.x provides mapping as
// "item/node" eg: "My Value"

// Feed Me 2.0 provides mapping as
// "item/node/value" eg: "My Value"
// "item/node/attributes/title" eg: "My Title"
```

As such, if you still only require the value for the node, you'll need to re-map against `item/node/value`.