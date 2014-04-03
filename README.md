# Netgen Class List Datatype

Netgen Class List Datatype is an eZ Publish extension that provides a datatype to select and store a list of content classes.

Suppose you are developing a Category class that has attributes which parametrise how fetching of children works. You would add a `class_filter_array` attribute (which is of `ngclasslist` datatype provided by the extension) to your Category class and would use it in the following way in the code:

```
{def $children = fetch(
    'content', 'list',
    hash(
        'parent_node_id', $node.data_map.parent_node_id.content,
        'class_filter_type', $node.data_map.class_filter_type.content,
        'class_filter_array', $node.data_map.class_filter_array.content.class_identifiers
    )
)}
```

## License and installation instructions
[License](LICENSE)

[Installation instructions](doc/INSTALL.md)
