# Component Design

This package components are built in json files, each json contains the following information

1. the names of the component (each component can have multiple names)
2. the fields
   1. `name: string` the name of the field
   2. `data-type: string` which decides the migration database row type
   3. `html-type: string` which decides the html input type
   4. `validation: string` which creates the validation for this field
   5. `nullable: boolean` which mean that this field can be null
   6. `index: boolean` which means this field should be on the index page table


##  html-type list and their data-type equivalents

1. `html-type="checkbox"` = `string` with an array of values
2. `html-type="radio"` = `enum` with an array of values
3. `html-type="date"` = `date`
4. `html-type="month"` = `date`
5. `html-type="week"` = `date`
6. `html-type="time"` = `time`
7. `html-type="text"` = `text` 
8. `html-type="textarea"` = `text` will render text area field
9. `html-type="email"` = `string`
10. `html-type="url"` = `text`
11. `html-type="tel"` = `string`
12. `html-type="color"` = `string`
13. `html-type="file"` = `string` *accept method to be developed*
14. `html-type="number"` = `integer` (type `float` adds `step="any"` to input)

## Example

This is the json file for `Article` component

``` json
{
    "names": [
        "Article","Blog","Essay"
    ],
    "fields": [
        {
            "name": "title",
            "data-type": "string",
            "html-type": "text",
            "validation": "required|string|min:1|max:255",
            "nullable": false,
            "index": true
        },
        {
            "name": "description",
            "data-type": "text",
            "html-type": "textarea",
            "validation": "nullable|min:3|max:1000",
            "nullable": true,
            "index": false
        },
        {
            "name": "body",
            "data-type": "text",
            "html-type": "textarea",
            "validation": "required|min:3",
            "nullable": false,
            "index": false
        },
        {
            "name": "thumbnail",
            "data-type": "string",
            "html-type": "file",
            "validation": "nullable|image",
            "nullable": true,
            "index": true
        }
    ]
}
```

You can find the full list of models here `src/Models`

## How to write a component

To write a component, you have to provide a content of a json file like the one in the example

if you want to provide a pull-request, you can add the component file *or files* inside `src/Models` and add the names array to `src/model_names.json`