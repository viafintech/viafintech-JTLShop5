<div class="s360-help">
    <h1>{__('Hilfe')}</h1>
    <div class="row">
        <div class="col-8">
            <h2>Webhook URL</h2>
            <div class="alert alert-info">{$webhook}</div>
        </div>
        <div class="col-4"></div>
    </div>
    <div class="row">
        <div class="col-8">
        <h2>{__('Zahlungsart')} {__('Einstellungen')}</h2>
        {if $apiConfig}
            <p class="alert alert-success">{__('paymethodConfigured')}</p>
            <p>{__('checkPaymethodSettings')}</p>
            <table class="table table-condensed">
                <thead><tr><td>{__('Land')}</td><td>{__('Modus')}</td><td>{__('Division-ID')}</td><td>{__('APIKey')}</td></tr></thead>
                {foreach $apiConfig as $country => $config}
                    <tr><td>{$country}</td><td>{if $config->sandbox}Sandbox{else}Live{/if}</td><td>{$config->divisionId}</td><td>{$config->APIKey}</td></tr>
                {/foreach}
            </table>
        {else}
            <p class="alert alert-warning">{__('paymethodNotConfigured')}</p>
            <ul>
                <li>{__('infoRegstration')} <a href="https://controlcenter.barzahlen.de">Barzahlen Control Center</a>.</li>
                <li>{__('infoAccountConfirmation')}</li>
                <li>{__('infoDivisionSetup')}</li>
                <li>{__('infoCredentials')}</li>
                <li>{__('infoSetupCredentials')}</li>
            </ul>   
        {/if}
        <div><a href="{$paymentMethodsUrl}" class="btn btn-primary">{__('Zu den Zahlarten')}</a></div>
        </div>
        <div class="col-4"></div>
    </div>
    <hr>
    <div class="row">    
        <div class="col-8">
        <h2>{__('Versandart')} {__('Einstellungen')}</h2>
        {if isset($tVersandarten)}
            <p class="alert alert-success">{__('shippingMethodActivated')}</p>
            <p>{__('checkShippingmethodSettings')}</p>
            <table class="table table-condensed">
                <thead><tr><td>{__('Versandart')}</td><td>{__('Länder')}</td></tr></thead>
            {foreach $tVersandarten as $Versandart}
                <tr><td>{$Versandart->cName}</td><td>{$Versandart->cLaender}</td></tr>
            {/foreach}
            </table>
        {else}
            <p class="alert alert-warning">{__('shippingmethodNotConfigured')}</p>  
        {/if}
        <div><a href="{$shippingMethodsUrl}" class="btn btn-primary">{__('Zu den Versandarten')}</a></div>
        </div>
        <div class="col-4"></div>
    </div>
    <div class="row">
        <div class="col">
        <p>{__('whereDocumentation')} <a href="https://solution360.atlassian.net/wiki/spaces/S360DOKU/pages/2591162451/Barzahlen+JTL-Shop+5">{__('Wiki')}</a>.</p>
        </div>
    </div>
</div>