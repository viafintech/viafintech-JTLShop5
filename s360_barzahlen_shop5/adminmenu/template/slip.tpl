<div class="modal hide fade in" id="slip-modal" tabindex="-1" role="dialog" aria-labelledby="{$slip->id}" aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{strtoupper($slip->id)}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6"><label>{__('Zahlung')}</label></div>
                    <div class="col-md-6 ml-auto"><label class="spaced">{__('Division-ID')}:</label> {$slip->division_id}</div>
                </div>
                <div class="row">
                    <div class="col-md-6"><label>{$slip->customer_key}</label></div>
                    <div class="col-md-6 ml-auto"><label class="spaced">{__('Erstellt am')}:</label> {$slip->created_at}</div>
                </div>
                <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 ml-auto"><label class="spaced">{__('Geändert am')}:</label> {$slip->updated_at}</div>
                </div>
                <div class="row">
                    <div class="col-md-3"><label>{__('Bestell-Nr')}:</label> {$slip->cBestellNr}</div>
                    <div class="col-md-3"><label>{__('Rechnungsland')}:</label> {$slip->cRechnungsLand}</div>
                    <div class="col-md-6 ml-auto"><label class="spaced">{__('Gültig bis')}:</label> {$slip->expires_at}</div>
                </div>
                <div class="row">
                    <div class="col-md-3"><label>{__('Betrag')}:</label> {$slip->transaction_amount} {$slip->transaction_currency}</div>
                    <div class="col-md-3"><label>{__('Lieferland')}:</label> {$slip->cLieferLand}</div>
                    <div class="col-md-6 ml-auto"><label class="spaced">{__('Status')}:</label> {$slip->transaction_state}
                    </div>
                </div>
                {if $slip->actions}    
                <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 ml-auto"><label class="spaced">{__('Aktionen')}:</label><span class="actions">
                        {if $slip->actions->refund}<a onclick="getRefundForm('{$slip->id}');" href="#" title="{__('Erstatten')}"><i class="fa fa-undo"></i></a>{/if}
                        {if $slip->actions->resend}<a onclick="confirmResendSlip('{$slip->id}');" href="#" title="{__('Senden')}"><i class="fa fa-share-square"></i></a>{/if}
                        {if $slip->actions->invalidate}<a onclick="confirmInvalidateSlip('{$slip->id}');" href="#" title="{__('Invalidieren')}"><i class="fa fa-times"></i></a>{/if}</span>
                    </div>
                </div>
                {/if}    
            </div>
            {if $slip->refunds}
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-2">{__('Erstellt am')}</div>
                    <div class="col-md-2">{__('Betrag')}</div>
                    <div class="col-md-2 text-right">{__('Gültig bis')}</div>
                    <div class="col-md-2">{__('Status')}</div>
                    <div class="col-md-2">{__('Aktionen')}</div>
                </div>
                {foreach $slip->refunds as $refund}
                <div class="row">
                    <div class="col-md-2">{__('Rückzahlung')}</div>
                    <div class="col-md-2">{$refund->created_at}</div>
                    <div class="col-md-2">{$refund->transaction_amount} {$refund->transaction_currency}</div>
                    <div class="col-md-2 text-right">{$refund->expires_at}</div>
                    <div class="col-md-2">{$refund->transaction_state}</div>
                    <div class="col-md-2"><span class="actions">
                    {if $refund->actions->resend}<a onclick="confirmResendSlip('{$refund->id}');" href="#" title="{__('Senden')}"><i class="fa fa-share-square"></i></a>{/if}
                    {if $refund->actions->invalidate}<a onclick="confirmInvalidateSlip('{$refund->id}');" href="#" title="{__('Invalidieren')}"><i class="fa fa-times"></i></a>{/if}</span>
                    </div>
                </div>
                {/foreach}
            </div>
            {/if}
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">{__('Schließen')}</button>
      </div>
        </div>
    </div>
</div>