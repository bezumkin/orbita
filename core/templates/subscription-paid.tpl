{extends 'email.tpl'}

{block 'content'}
    {var $date = strtotime($subscription.active_until) | date : 'd.m.Y'}
    {if $lang === 'de'}
        <h2>Vielen Dank!</h2>
        <p>
            Sie haben Ihr Abonnement erfolgreich bezahlt "{$level.title}" bis {$date}
        </p>
    {elseif $lang === 'ru'}
        <h2>Спасибо!</h2>
        <p>
            Вы успешно оплатили подписку "{$level.title}" до {$date}
        </p>
    {else}
        <h2>Thank you!</h2>
        <p>
            You have successfully paid for your subscription "{$level.title}" until {$date}
        </p>
    {/if}
{/block}