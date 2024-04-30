{extends 'email.tpl'}

{block 'content'}
    {if $lang === 'de'}
        <h2>Registrierung bestätigen</h2>
    {elseif $lang === 'ru'}
        <h2>Подтверждение регистрации</h2>
    {else}
        <h2>Confirm registration</h2>
    {/if}

    {var $link = $.env.SITE_URL ~ 'user/confirm/' ~ $user.username ~ '/' ~ $code}
    <p>
        <a href="{$link}">{$link}</a>
    </p>
{/block}