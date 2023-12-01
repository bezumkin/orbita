{extends 'email.tpl'}

{block 'content'}
    <h2>{$topic.title}</h2>

    {if $topic.content}
        <div class="preview">{$topic.content | content_preview | truncate : 1000}</div>
    {/if}

    {if $lang === 'en'}
        <p>Please follow <a href="{$topic.link}">this link</a>, to read it.</p>
    {else}
        <p>Пожалуйста, пройдите <a href="{$topic.link}">по этой ссылке</a>, чтобы его прочитать.</p>
    {/if}
{/block}