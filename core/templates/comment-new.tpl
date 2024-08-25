{extends 'email.tpl'}

{block 'content'}
    {if $lang === 'de'}
        <p>
            User <strong>{$user.fullname}</strong> schreibt einen Kommentar zu deinem Thema
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content}</div>
        {/if}
        <p>Bitte folgen <a href="{$comment.link}">dem Link</a>, antworten.</p>
    {elseif $lang === 'ru'}
        <p>
            Пользователь <strong>{$user.fullname}</strong> оставил комментарий к вашей заметке
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content}</div>
        {/if}
        <p>Пожалуйста, пройдите <a href="{$comment.link}">по ссылке</a>, чтобы ответить.</p>
    {else}
        <p>
            User <strong>{$user.fullname}</strong> left a comment on your topic
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content}</div>
        {/if}
        <p>Please follow <a href="{$comment.link}">this link</a>, to reply.</p>
    {/if}
{/block}