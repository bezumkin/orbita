{extends 'email.tpl'}

{block 'content'}
    {if $lang === 'de'}
        <p>
            Benutzer <strong>{$user.fullname}</strong> hat auf Ihren Kommentar in der geantwortet
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content}</div>
        {/if}
        <p>Bitte folgen <a href="{$comment.link}">dem Link</a>, antworten.</p>
    {elseif $lang === 'ru'}
        <p>
            Пользователь <strong>{$user.fullname}</strong> ответил на ваш комментарий в заметке
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content}</div>
        {/if}
        <p>Пожалуйста, пройдите <a href="{$comment.link}">по этой ссылке</a>, чтобы ответить.</p>
    {else}
        <p>
            User <strong>{$user.fullname}</strong> responded to your comment in the
            <a href="{$topic.link}">"{$topic.title}"</a>:
        </p>
        {if $comment.content}
            <div class="preview">{$comment.content}</div>
        {/if}
        <p>Please follow <a href="{$comment.link}">this link</a>, to reply.</p>
    {/if}
{/block}