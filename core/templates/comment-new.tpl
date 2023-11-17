{extends 'email.tpl'}

{block 'content'}
    {if $lang === 'en'}
        <p>
            User <strong>{$user.fullname}</strong> left a comment on your topic
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content | content_preview | truncate : 1000}</div>
        {/if}
        <p>Please follow <a href="{$comment.link}">this link</a>, to read it.</p>
    {else}
        <p>
            Пользователь <strong>{$user.fullname}</strong> оставил комментарий к вашей заметке
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content | content_preview | truncate : 1000}</div>
        {/if}
        <p>Пожалуйста, пройдите <a href="{$comment.link}">по ссылке</a>, чтобы его прочитать.</p>
    {/if}
{/block}