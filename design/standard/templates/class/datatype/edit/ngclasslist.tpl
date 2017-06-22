{def $class_list_by_group = $class_attribute.content.classes_by_group}
{def $selected_class_identifiers = $class_attribute.content.selected_class_identifiers}
{def $selected_allowed_groups = $class_attribute.content.allowed_groups}

<div class="block">
    <label for="ContentClass_ngclasslist_class_list_default_value">{'Default value'|i18n( 'design/standard/class/datatype' )}:</label>

    <select multiple="multiple" size="15" class="box" name="ContentClass_ngclasslist_class_list_default_value_{$class_attribute.id}[]" id="ContentClass_ngclasslist_class_list_default_value">
        <option value=""></option>
        {foreach $class_list_by_group as $group_name => $group_classes}
            <option disabled="disabled" value="group_{$group_name|wash}">{$group_name|wash}</option>
            {foreach $group_classes as $class}
                <option value="{$class.identifier|wash}"{if $selected_class_identifiers|contains( $class.identifier )} selected="selected"{/if}>&nbsp;&nbsp;&nbsp;{$class.name|wash}</option>
            {/foreach}
        {/foreach}
    </select>

</div>


<div class="block">
    <label for="ContentClass_ngclasslist_class_list_allowed">{'Allowed groups'|i18n( 'extension/ngclasslist/datatypes' )}:</label>

    <select multiple="multiple" size="15" class="box" name="ContentClass_ngclasslist_allowed_groups_{$class_attribute.id}[]" id="ContentClass_ngclasslist_class_list_allowed">
        <option value=""></option>
        {foreach $class_list_by_group as $group_name => $group_classes}
            {if $group_name|ne('')}
                <option {if $selected_allowed_groups|contains( $group_name )} selected="selected"{/if} value="{$group_name|wash}">{$group_name|wash}</option>
            {/if}
        {/foreach}
    </select>

</div>

{undef $class_list_by_group}
