{if $attribute.has_content}
    {foreach $attribute.content.classes as $class}
        <p><a href={concat( 'class/view/', $class.id )}>{$class.name|wash}</a></p>
    {/foreach}
{else}
    {'No classes'|i18n( 'extension/ngclasslist/datatypes' )}
{/if}
