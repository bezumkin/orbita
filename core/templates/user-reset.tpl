{extends 'email.tpl'}

{block 'content'}
    {if $lang === 'de'}
        <h2>Login-Link</h2>
    {elseif $lang === 'ru'}
        <h2>Ссылка для входа</h2>
    {else}
        <h2>Login link</h2>
    {/if}

    {var $link = $.env.SITE_URL ~ 'user/confirm/' ~ $user.username ~ '/' ~ $code}
    <p>
        <a href="{$link}">{$link}</a>
    </p>
{/block}