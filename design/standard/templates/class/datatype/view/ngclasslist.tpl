<div class="block">
{def $class_attribute_content = $class_attribute.content}
    {if count($class_attribute_content.selected_class_identifiers)|gt(0)}
    <div class="element">
        <label>{'Default value'|i18n( 'design/standard/class/datatype' )}:</label>
        <p>{$class_attribute_content.selected_class_identifiers|implode(', ')|wash}</p>
    </div>
    {/if}

    {if count($class_attribute_content.allowed_groups)|gt(0)}
    <div class="element">
        <label>{'Allowed groups'|i18n( 'extension/ngclasslist/datatypes' )}:</label>
        <p>{$class_attribute_content.allowed_groups|implode(', ')|wash}</p>
    </div>
    {/if}
{undef $class_attribute_content}
</div>
