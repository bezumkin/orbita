{extends 'email.tpl'}

{block 'content'}
    {var $link = $.env.SITE_URL}
    {if $lang === 'en'}
        <h2>Very sorry...</h2>
        {if $renew}
            We couldn't charge the money for the subscription "{$level.title}".
        {else}
            Your subscription "{$level.title}" has ended.
        {/if}
        <p>
            You can renew your subscription manually on <a href="{$link}">the website</a>.
        </p>
    {else}
        <h2>Очень жаль...</h2>
        {if $renew}
            Мы не смогли списать деньги на подписку "{$level.title}".
        {else}
            Ваша подписка "{$level.title}" закончилась.
        {/if}
        <p>
            Вы можете продлить подписку вручную <a href="{$link}">на сайте</a>.
        </p>
    {/if}
{/block}