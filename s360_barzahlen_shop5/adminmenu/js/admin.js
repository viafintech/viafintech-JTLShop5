
    function confirmResendSlip(slipId){
        $(".modal").modal('hide');
        $('.resend').show();
        $('#s360-confirmed-resend').data("slipid", slipId);
        $('#confirm-modal').modal();
    }
    
    function confirmInvalidateSlip(slipId){
        $(".modal").modal('hide');
        $('.invalidate').show();
        $('#s360-confirmed-invalidate').data("slipid", slipId);
        $('#confirm-modal').modal();
    }
    
    function confirmAbort(){
        $(".modal").modal('hide');
        $('.element').hide();
        $('#s360-confirmed-resend').data("slipid", "");
        $('#s360-confirmed-invalidate').data("slipid", "");
        clearMessage();
    }
    
    function clearMessage() {
        var message = $('#s360-message');
        message.html('');
        return message;
    }
    
    function resendSlip(slipId) {
        var message = clearMessage();
        $.ajax({
            url: window.ajaxEndpoint,
            method: 'POST',
            data: {
                action: 'resendSlip',
                slipId: slipId
            }
        }).done(function (data) {
            $(".modal").modal('hide');
            if (data.result === 'success') {
                message.html('<div class="alert alert-success">'+window.__MSG['slipResend']+'</div>');
            } else {
                message.html('<div class="alert alert-danger">'+data.message+'</div>');
            }
        }).fail(function (jqXHR, textStatus) {
            message.html('<div class="alert alert-danger">Request error: ' + textStatus + '</div>');
        });
    }

    function invalidateSlip(slipId) {
        var message = clearMessage();
        $.ajax({
            url: window.ajaxEndpoint,
            method: 'POST',
            data: {
                action: 'invalidateSlip',
                slipId: slipId
            }
        }).done(function (data) {
            $(".modal").modal('hide');
            var page = $("#pagination").data("page");
            loadOrders(page);
            if (data.result === 'success') {
                message.html('<div class="alert alert-success">'+window.__MSG['slipInvalidated']+'</div>');
            } else {
                message.html('<div class="alert alert-danger">'+data.message+'</div>');
            }
        }).fail(function (jqXHR, textStatus) {
            message.html('<div class="alert alert-danger">Request error: ' + textStatus + '</div>');
        });
    }

    function performRefund(slipId) {
       var message = clearMessage();
        var refundValue = $('#refund-value').val();
        $.ajax({
            url: window.ajaxEndpoint,
            method: 'POST',
            data: {
                action: 'performRefund',
                slipId: slipId,
                refundValue: refundValue
            }
        }).done(function (data) {
            $(".modal").modal('hide');
            var page = $("#pagination").data("page");
            loadOrders(page);
            if (data.result === 'success') {
                message.html('<div class="alert alert-success">'+window.__MSG['slipRefunded']+'</div>');
            } else {
                message.html('<div class="alert alert-danger">'+data.message+'</div>');
            }
        }).fail(function (jqXHR, textStatus) {
            message.html('<div class="alert alert-danger">Request error: ' + textStatus + '</div>');
        });
    }

    function getRefundForm(slipId) {
        $(".modal").modal('hide');
        var message = clearMessage();
        var $container = $('#s360-modal');
        $.ajax({
            url: window.ajaxEndpoint,
            method: 'POST',
            data: {
                action: 'getRefundForm',
                slipId: slipId
            }
        }).done(function (data) {
            if (data.result === 'success') {
                $container.html(data.html);
                $("#refund-modal").modal();
            } else {
                message.html('<div class="alert alert-danger">'+data.message+'</div>');
            }
        }).fail(function (jqXHR, textStatus) {
            message.html('<div class="alert alert-danger">Request error: ' + textStatus + '</div>');
        });
    }

    function getSlipInfo(slipId) {
        var message = clearMessage();
        var container = $('#s360-modal');
        $.ajax({
            url: window.ajaxEndpoint,
            method: 'POST',
            data: {
                action: 'getSlipInfo',
                slipId: slipId
            }
        }).done(function (data) {
            if (data.result === 'success') {
                container.html(data.html);
                $("#slip-modal").modal();
            } else {
                message.html('<div class="alert alert-danger">'+data.message+'</div>');
            }
        }).fail(function (jqXHR, textStatus) {
            message.html('<div class="alert alert-danger">Request error: ' + textStatus + '</div>');
        });
    }

    function loadOrders(page) {
        var message = clearMessage();
        var container = $('#s360-slip-list');
        $("#pagination").data("page", page); //set next page
        $('#s360-slip-table').hide();
        $('.s360-loading-indicator').show();
        $.ajax({
            url: window.ajaxEndpoint,
            method: 'POST',
            data: {
                action: 'loadOrders',
                page: page
            }
        }).done(function (data) {
            if (data.result === 'success') {
                container.html(data.html);
            } else {
                message.html('<div class="alert alert-danger">'+data.message+'</div>');
            }
        }).fail(function (jqXHR, textStatus) {
            message.html('<div class="alert alert-danger">Request error: ' + textStatus + '</div>');
        }).always(function () {
            $('.s360-loading-indicator').hide();
            $('#s360-slip-table').show();
        });
    }
    
    function searchOrders(input) {
        var message = clearMessage();
        var container = $('#s360-slip-list');
        
        $('#s360-slip-table').hide();
        $('.s360-loading-indicator').show();
        $.ajax({
            url: window.ajaxEndpoint,
            method: 'POST',
            data: {
                action: 'searchOrders',
                search: input
            }
        }).done(function (data) {
            if (data.result === 'success') {
                container.html(data.html);
            } else {
                message.html('<div class="alert alert-danger">'+data.message+'</div>');
            }
        }).fail(function (jqXHR, textStatus) {
            message.html('<div class="alert alert-danger">Request error: ' + textStatus + '</div>');
        }).always(function () {
            $('.s360-loading-indicator').hide();
            $('#s360-slip-table').show();
        });
    }

(function ($) {

    $(document).ajaxComplete(function () {
        var page = $("#pagination").data("page");
        var maxpage = $("#maxpage").data("maxpage");
        if (page>0 && maxpage>0) {
            var i, options;
            for (i=1; i<=maxpage; i++) {
                if (i===page) {
                    options += "<option data-page='"+i+"' selected>"+i+"</option>";
                } else {
                    options += "<option data-page='"+i+"'>"+i+"</option>";
                }
            }
            $("#page-select").html(options);
            $("#page-item-current").show();
            if (page+1<=maxpage) {
                $("#page-item-last").show();
                $("#page-item-next").show();
            }
            if (page-1>=1) {
               $("#page-item-prev").show();
               $("#page-item-first").show();
            }
        }
        
        $("#s360-confirmed-resend").unbind('click').bind('click', function(e) {
            e.preventDefault();
            $('.modal').modal('hide');
            $(".element").hide();
            var slipId = $("#s360-confirmed-resend").data('slipid');
            resendSlip(slipId);
        });
        $("#s360-confirmed-invalidate").unbind('click').click(function(e) {
            e.preventDefault();
            $('.modal').modal('hide');
            $(".element").hide();
            var slipId = $("#s360-confirmed-invalidate").data('slipid');
            invalidateSlip(slipId);
        });
        
    });

    $(document).ready(function () {

        $(".page-item").hide();
        var page = $("#pagination").data("page");
        loadOrders(page);

        $(".page-link-first").click(function(e) {
            e.preventDefault();
            $(".page-item").hide();
            loadOrders(1);
        });
        
        $("#page-link-prev").click(function(e) {
            e.preventDefault();
            $(".page-item").hide();
            var page = $("#pagination").data("page");
            if ( page>1 ) { 
                loadOrders(page-1);
            }
        });
        
        $("#page-select").change(function(){
            $(".page-item").hide();
            var page = $(this).find(':selected').data('page');
            loadOrders(page);
        });
        
        $("#page-link-next").click(function(e) {
            e.preventDefault();
            $(".page-item").hide();
            var page = $("#pagination").data("page");
            var maxpage = $("#maxpage").data("maxpage");
            if ( page<maxpage ) { 
                loadOrders(page+1);
            } 
        });
        
        $("#page-link-last").click(function(e) {
            e.preventDefault();
            $(".page-item").hide();
            var maxpage = $("#maxpage").data("maxpage");
            loadOrders(maxpage);
        });

        $("#s360-search").bind('keyup', function(e) {
            if(e.keyCode === 13) {
                $("#s360-search-button").click();
            }
        });

        $("#s360-search-button").click(function(e) {
            e.preventDefault();
            var input = $("#s360-search").val();
            if (input.length >= 3) {
                $(".page-item").hide();
                $("#page-item-reset").show();
                $("#pagination").data("page", 0);
                searchOrders(input);
                $("#s360-search").val('');
            } else {
                alert(window.__MSG['minInput']);
            }
        });

    });           

})(jQuery);
