{extends 'email.tpl'}

{block 'content'}
    <h2>{$topic.title}</h2>

    {if $topic.teaser}
        <div class="teaser">{$topic.teaser}</div>
    {/if}

    {if $topic.cover}
        <div class="cover">
            <img src="{$topic.cover}" alt="" />
        </div>
    {/if}

    {if $lang === 'de'}
        <p>Bitte folgen Sie <a href="{$topic.link}">diesem Link</a>, um es anzuzeigen.</p>
    {elseif $lang === 'ru'}
        <p>Пожалуйста, пройдите <a href="{$topic.link}">по этой ссылке</a>, чтобы ознакомиться.</p>
    {else}
        <p>Please follow <a href="{$topic.link}">this link</a>, to view.</p>
    {/if}
{/block}