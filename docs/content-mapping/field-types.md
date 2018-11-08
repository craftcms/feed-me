# Field Types

Feed Me supports all native [Craft Fields](https://craftcms.com/docs/fields), and even some third-party ones.

### Assets

Accepts single or multiple values. You should supply the filename only, excluding the full path to the asset, but including the filename. If you're uploading remote assets, you'll need to produce fully-qualified URLs.

#### Additional Options

- Upload remote asset (choose how to handle existing assets - Replace/Keep/Ignore)
- [Inner-element fields](docs:content-mapping/field-types#inner-element-fields)

:::code
```xml
<Asset>my_filename.jpg</Asset>

// Or
<Assets>
    <Asset>my_filename.jpg</Asset>
    <Asset>my_other_filename.jpg</Asset>
</Assets>

//
// When selecting upload
//
<Asset>http://mydomain.com/my_filename.jpg</Asset>

// Or
<Assets>
    <Asset>http://mydomain.com/my_filename.jpg</Asset>
    <Asset>http://mydomain.com/my_other_filename.jpg</Asset>
</Assets>
```

```json
{
    "Asset": "my_filename.jpg"
}

// Or
{
    "Assets": [
        "my_filename.jpg",
        "my_other_filename.jpg"
    ]
}

//
// When selecting upload
//
{
    "Asset": "http://mydomain.com/my_filename.jpg"
}

// Or
{
    "Assets": [
        "http://mydomain.com/my_filename.jpg",
        "http://mydomain.com/my_other_filename.jpg"
    ]
}
```
:::

### Categories

Accepts single or multiple values.

#### Additional Options

- Create category if it does not exist
- [Inner-element fields](docs:content-mapping/field-types#inner-element-fields)
- [Set element attribute](docs:content-mapping/field-types#inner-element-fields) for data being imported
- Title
- ID
- Slug

:::code
```xml
<Category>My Category</Category>

// Or
<Categories>
    <Category>My Category</Category>
    <Category>Another Category</Category>
</Categories>
```

```json
{
    "Category": "My Category"
}

// Or
{
    "Categories": [
        "My Category",
        "Another Category"
    ]
}
```
:::

### Checkboxes

Accepts single or multiple values. You must provide the Value of the option to check, not the Label.

:::code
```xml
<Checkbox>option1</Checkbox>

// Or
<Checkboxes>
    <Option>option1</Option>
    <Option>option2</Option>
</Checkboxes>
```

```json
{
    "Checkbox": "option1"
}

// Or
{
    "Checkboxes": [
        "option1",
        "option2"
    ]
}
```
:::

### Color

Accepts a single valid Colour value - usually in Hexadecimal.

:::code
```xml
<Color>#ffffff</Color>
```

```json
{
    "Color": "#ffffff"
}
```
:::

### Date/Time
Accepts a single valid date and time string. Supports many different formats, using PHP's [date\_parse](http://php.net/manual/en/function.date-parse.php) function.

:::code
```xml
<Date>Tue, 24 Feb 2015 09:00:53 +0000</Date>
<Date>2015-02-24 09:00:53</Date>
<Date>02/24/2015</Date>
```

```json
{
    "Date": "Tue, 24 Feb 2015 09:00:53 +0000"
}

{
    "Date": "2015-02-24 09:00:53"
}

{
    "Date": "02/24/2015"
}
```
:::

### Dropdown

Accepts a single value. You must provide the Value of the option to select, not the Label.

:::code
```xml
<Dropdown>option2</Dropdown>
```

```json
{
    "Dropdown": "option2"
}
```
:::

### Entries
Accepts single or multiple values.

#### Additional Options

- Create entry if it does not exist
- [Inner-element fields](docs:content-mapping/field-types#inner-element-fields)
- [Set element attribute](docs:content-mapping/field-types#inner-element-fields) for data being imported
- Title
- ID
- Slug

:::code
```xml
<Entry>My Entry</Entry>

// Or
<Entries>
    <Entry>My Entry</Entry>
    <Entry>Another Entry</Entry>
</Entries>
```

```json
{
    "Entry": "My Entry"
}

// Or
{
    "Entries": [
        "My Entry",
        "Another Entry"
    ]
}
```
:::

### Lightswitch

Accepts a single value. Can be provided as any boolean-like string.

:::code
```xml
// 1/0
<Lightswitch>1</Lightswitch>

// true/false
<Lightswitch>true</Lightswitch>

// Yes/No
<Lightswitch>Yes</Lightswitch>
```

```json
// 1/0
{
    "Lightswitch": "1"
}

// true/false
{
    "Lightswitch": "true"
}

// Yes/No
{
    "Lightswitch": "Yes"
}
```
:::

### Matrix

Check out [Importing into Matrix](docs:guides/importing-into-matrix) for a more comprehensive guide.

### Multi-select

Accepts single or multiple values. You must provide the Value of the option to select, not the Label.

:::code
```xml
<MultiSelect>option1</MultiSelect>

// Or
<MultiSelects>
    <MultiSelect>option1</MultiSelect>
    <MultiSelect>option2</MultiSelect>
</MultiSelects>
```

```json
{
    "MultiSelect": "option1"
}

// Or
{
    "MultiSelects": [
        "option1",
        "option2"
    ]
}
```
:::

### Number

Accepts a single value.

:::code
```xml
<Number>10</Number>
```

```json
{
    "Number": "10"
}
```
:::

### Plain Text

Accepts a single value.

:::code
```xml
<PlainText>Lorem ipsum dolor sit amet</PlainText>
```

```json
{
    "PlainText": "Lorem ipsum dolor sit amet"
}
```
:::

### Position Select

Accepts a single or multiple values. Should provide from the below options:

- `left`
- `center`
- `right`
- `full`
- `drop-left`
- `drop-right`

:::code
```xml
<Position>right</Position>

// Or
<Positions>
    <Position>left</Position>
    <Position>right</Position>
</Positions>
```

```json
{
    "Position": "right"
}

// Or
{
    "Positions": [
        "left",
        "right"
    ]
}
```
:::

### Radio Buttons

Accepts a single value. You must provide the Value of the option to select, not the Label.

:::code
```xml
<Radio>option2</Radio>
```

```json
{
    "Radio": "option2"
}
```
:::

### Rich Text

Accepts a single value. Be sure to escape your content properly if it contains HTML.

:::code
```xml
<RichText><![CDATA[<p>Lorem ipsum dolor sit amet.</p>]]></RichText>
```

```json
{
    "RichText": "<p>Lorem ipsum dolor sit amet.</p>"
}
```
:::

### Table

Each Table field row has multiple columns, so you map each field value to a column, rather than the entire Table field. You also group your columns into rows, as shown below.

:::code
```xml
<Table>
    <Row>
        <ColumnOne>Content</ColumnOne>
        <ColumnTwo>For</ColumnTwo>
    </Row>

    <Row>
        <ColumnOne>Table</ColumnOne>
        <ColumnTwo>Field</ColumnTwo>
    </Row>
</Table>
```

```json
{
    "Table": [{
        "ColumnOne": "Content",
        "ColumnTwo": "For"
    },{
        "ColumnOne": "Table",
        "ColumnTwo": "Field"
    }]
}
```
:::

### Tags

Accepts single or multiple values.

#### Additional Options

- Create tag if it does not exist
- [Inner-element fields](docs:content-mapping/field-types#inner-element-fields)

:::code
```xml
<Tag>My Tag</Tag>

// Or
<Tags>
    <Tag>First Tag</Tag>
    <Tag>Second Tag</Tag>
</Tags>
```

```json
{
    "Tag": "My Tag"
}

// Or
{
    "Tags": [
        "First Tag",
        "Second Tag"
    ]
}
```
:::

### Users

Accepts single or multiple values.

#### Additional Options

- Create user if they do not exist
- [Inner-element fields](docs:content-mapping/field-types#inner-element-fields)
- [Set element attribute](docs:content-mapping/field-types#inner-element-fields) for data being imported
- Email
- ID
- Username
- Full Name

:::code
```xml
<User>123@nothing.com</User>

// Or
<Users>
    <User>123@nothing.com</User>
    <User>123@something.com</User>
</Users>
```

```json
{
    "User": "123@nothing.com"
}

// Or
{
    "Users": [
        "123@nothing.com",
        "123@something.com"
    ]
}
```
:::

## Third Party

- [Smart Map](https://craftpl.us/plugins/smart-map/)
- [Super Table](https://github.com/engram-design/SuperTable)

## Element Attributes

For element fields (Assets, Categories, Entries, Tags and Users), you'll want to check against any existing elements. Feed Me gives you the flexibility to choose how to match against existing elements. These will depend on what element it is, but will often be `slug` or `title`.

What this means in practical terms, is that your feed data can provide the ID, Title or Slug of an Entry - or the ID, Username, Name or Email for a User, and so on.

For instance, look at the following example feed data we want to import into a Categories field:

:::code
```xml
// Title provided
<Category>My Category</Category>

// Slug provided
<Category>my-category</Category>

// ID provided
<Category>23</Category>
```

```json
// Title provided
{
    "Category": "My Category"
}

// Slug provided
{
    "Category": "my-category"
}

// ID provided
{
    "Category": "23"
}
```
:::

Depending on what data your feed contains, you'll need to select the appropriate attribute, to tell Feed Me how to deal with your data.

## Inner Element Fields

As each Element (Assets, Categories, Entries, Tags, Users) can have custom fields themselves, Feed Me gives you the chance to map to those fields as well. They'll appear under any row when mapping to an Element field.
