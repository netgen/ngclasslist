{default attribute_base='ContentObjectAttribute' html_class='full'}
{def $class_list_by_group = $attribute.contentclass_attribute.content.classes_by_group}
{def $allowed_groups = $attribute.contentclass_attribute.content.allowed_groups}
    
<select multiple="multiple" size="15" id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" class="{eq( $html_class, 'half' )|choose( 'box', 'halfbox' )} ezcc-{$attribute.object.content_class.identifier} ezcca-{$attribute.object.content_class.identifier}_{$attribute.contentclass_attribute_identifier}" name="{$attribute_base}_ngclasslist_class_list_{$attribute.id}[]">
    {foreach $class_list_by_group as $group_name => $group_classes}
        {if or(count($allowed_groups)|eq(0), $allowed_groups|contains($group_name))}
            <option disabled="disabled" value="group_{$group_name|wash}">{$group_name|wash}</option>
            {foreach $group_classes as $class}
                <option value="{$class.identifier|wash}"{if $attribute.content.class_identifiers|contains( $class.identifier )} selected="selected"{/if}>&nbsp;&nbsp;&nbsp;{$class.name|wash}</option>
            {/foreach}
        {/if}
    {/foreach}
</select>

{undef $class_list_by_group $class_list}
{/default}
