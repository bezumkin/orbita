{extends 'email.tpl'}

{block 'content'}
    {var $link = $.env.SITE_URL ~ 'service/confirm/' ~ $user.username ~ '/' ~ $code}
    {if $lang === 'en'}
        <h2>Confirm registration</h2>
        <p>
            <a href="{$link}">{$link}</a>
        </p>
    {else}
        <h2>Подтверждение регистрации</h2>
        <p>
            <a href="{$link}">{$link}</a>
        </p>
    {/if}
{/block}