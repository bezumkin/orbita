{extends 'email.tpl'}

{block 'content'}
    {var $link = $.env.SITE_URL}
    {if $lang === 'de'}
        <h2>Tut mir sehr leid...</h2>
        {if $renew}
            Wir konnten das Geld für das Abonnement nicht berechnen "{$level.title}".
        {else}
            Ihr Abonnement ist "{$level.title}" beendet.
        {/if}
        <p>
            Sie können Ihr Abonnement auf der Website <a href="{$link}">manuell verlängern</a>.
        </p>
    {elseif $lang === 'ru'}
        <h2>Очень жаль...</h2>
        {if $renew}
            Мы не смогли списать деньги на подписку "{$level.title}".
        {else}
            Ваша подписка "{$level.title}" закончилась.
        {/if}
        <p>
            Вы можете продлить подписку вручную <a href="{$link}">на сайте</a>.
        </p>
    {else}
        <h2>Very sorry...</h2>
        {if $renew}
            We couldn't charge the money for the subscription "{$level.title}".
        {else}
            Your subscription "{$level.title}" has ended.
        {/if}
        <p>
            You can renew your subscription manually on <a href="{$link}">the website</a>.
        </p>
    {/if}
{/block}