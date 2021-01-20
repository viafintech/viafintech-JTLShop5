<script>
    function confirmOK(){
        $('.element').hide();
        $('.confirm').show();
    }
    function revertOK(){
        $('.element').show();
        $('.confirm').hide();
    }
</script>

<div class="modal fade" id="refund-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">{__('Rückzahlung für Bestellung')} {$slip->cBestellNr}</h4>
      </div>
      <div class="modal-body element">
            {__('Betrag erstatten')}: <input id="refund-value" class="form-control" style="display:inline; width:20%;" type="text" name="refundValue" value="{$slip->transaction_amount}" placeholder="{$slip->transaction_amount}"> {$slip->transaction_currency}
      </div>
      <div class="modal-body confirm" style="display:none;">
          {__('Möchten Sie die Rückzahlung jetzt anweisen?')}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary element" data-dismiss="modal" onclick="confirmAbort();">{__('Schließen')}</button>
        <button type="button" class="btn btn-primary element" onclick="confirmOK();">{__('OK')}</button>
        <button type="button" class="btn btn-secondary confirm" style="display:none;" onclick="revertOK();">{__('Nein')}</button>
        <button type="button" class="btn btn-danger confirm" style="display:none;" onclick="performRefund('{$slip->for_slip_id}');">{__('Erstatten')}</button>
      </div>
    </div>
  </div>
</div>