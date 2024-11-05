{extends 'email.tpl'}

{block 'content'}
    {var $link = $.env.SITE_URL ~ 'user/subscription'}
    {var $date = strtotime($subscription.active_until) | date : 'd.m.Y'}
    {var $days = $.env.SUBSCRIPTION_WARN_BEFORE_DAYS ?: 3}
    {if $lang === 'de'}
        <h2>Warnung!</h2>
        <p>Ihr "{$level.title}" -Abonnement wird in {$days | declension : 'tag|tagen'}, {$date}, erneuert.</p>
        <p>Sie können die Verlängerung <a href="{$link}">in den Einstellungen auf der Website</a> abbrechen.</p>
    {elseif $lang === 'ru'}
        <h2>Внимание!</h2>
        <p>Ваша подписка "{$level.title}" будет возобновлена через {$days | declension : 'день|дня|дней'}, {$date}.</p>
        <p>Вы можете отказаться от продления <a href="{$link}">в настройках на сайте</a>.</p>
    {else}
        <h2>Warning!</h2>
        <p>Your "{$level.title}" subscription will be renewed in {$days | declension : 'day|days'}, on {$date}.</p>
        <p>You can cancel the renewal <a href="{$link}">in the settings on the website</a>.</p>
    {/if}
{/block}