<script>
     window.__MSG = {
        'slipResend': '{__('slipResend')}', 
        'slipInvalidated': '{__('slipInvalidated')}', 
        'slipRefunded': '{__('slipRefunded')}', 
        'minInput': '{__('minInput')}'
    };
</script>
<link rel="stylesheet" type="text/css" href="{$AdminmenuPfad}css/admin.css">
<script type="text/javascript" src="{$AdminmenuPfad}js/admin.js" async="async"></script>
<script type="text/javascript">
window.ajaxEndpoint = '{$AdminPluginURL}&isAjax=1';
</script>

<div>
    <h1>{__('Zahlungen')}</h1>
    <div id="s360-message"></div>
    <div id="s360-barzahlen-orders">
        {* Orders overview *}
        <div>
            <div class="col-xs-12">
                <div class="text-center">
                    <div class="input-group">
                        <input id="s360-search" type="text" class="form-control" placeholder="{__('Bestell-Nr')}/{__('Zahlschein')}/{__('Betrag')}">
                        <span class="input-group-btn">
                            <button id="s360-search-button" class="btn btn-primary" type="button">{__('Suchen')}</button>
                        </span>
                    </div>                    
                </div>
                <table id="s360-slip-table" class="table table-condensed">
                    <thead>
                        <td>{__('Bestell-Nr')}</td>
                        <td>{__('Zahlschein')}</td>
                        <td>{__('Division')}</td>
                        <td>{__('Typ')}</td>
                        <td class="text-center">{__('Status')}</td>
                        <td class="text-right">{__('Betrag')}</td>
                        <td class="text-right">{__('Storno')}</td>
                        <td class="text-center">{__('Land')}</td>
                        <td class="text-center">{__('Lieferung')}</td>
                        <td>{__('Ablauf')}</td>
                        <td>{__('Aktionen')}</td>
                    </thead>
                    <tbody id="s360-slip-list">
                        {* See payments_inc.tpl for content *}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-center">
            <ul class="pagination justify-content-center" id="pagination" data-page="{$page}" aria-label="Page navigation">
                <li id="page-item-first" class="page-item"><a class="page-link page-link-first" href="#"><span>&laquo;</span></a></li>
                <li id="page-item-prev" class="page-item"><a id="page-link-prev" class="page-link" href="#"><span>&lsaquo;</span></a></li>
                <li id="page-item-current" class="page-item"><a id="page-link-current" class="page-link"><select id="page-select"></select></a></li>
                <li id="page-item-reset" class="page-item"><a class="page-link page-link-first" href="#"><span>{__('Zurücksetzen')}</span></a></li>
                <li id="page-item-next" class="page-item"><a id="page-link-next" class="page-link" href="#"><span>&rsaquo;</span></a></li>
                <li id="page-item-last" class="page-item"><a id="page-link-last" class="page-link" href="#"><span>&raquo;</span></a></li>
            </ul>
        </div>
        <div id="s360-modal"></div>
        <div id="s360-confirm">
            <div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title">{__('Bestätigung')}</h4>
                        </div>
                        <div class="modal-body element resend" style="display:none;">
                            {__('Möchten Sie jetzt den Zahlschein erneut versenden?')}
                        </div>
                        <div class="modal-body element invalidate" style="display:none;">
                            {__('Möchten Sie wirklich den Zahlschein invalidieren?')}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="confirmAbort();">{__('Nein')}</button>
                            <button type="button" class="btn btn-primary element resend" style="display:none;" id="s360-confirmed-resend">{__('Senden')}</button>
                            <button type="button" class="btn btn-danger element invalidate" style="display:none;" id="s360-confirmed-invalidate">{__('Invalidieren')}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="s360-loading-indicator text-center" style="display:none;">
            <div class="fa fa-spinner fa-pulse"></div>
        </div>
    </div>
</div>
                    
                    
