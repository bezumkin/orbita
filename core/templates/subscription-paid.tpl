{extends 'email.tpl'}

{block 'content'}
    {if $lang === 'en'}
        <h2>Thank you!</h2>
        <p>
            You have successfully paid for your subscription "{$level.title}" until
            {$subscription.active_until | date_format : 'd.m.Y'}
        </p>
    {else}
        <h2>Спасибо!</h2>
        <p>
            Вы успешно оплатили подписку "{$level.title}" до
            {$subscription.active_until | date_format : 'd.m.Y'}
        </p>
    {/if}
{/block}