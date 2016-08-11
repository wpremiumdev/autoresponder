jQuery(document).ready(function () {
    jQuery('form').each(function () {
        var cmdcode = jQuery(this).find('input[name="cmd"]').val();
        var bncode = jQuery(this).find('input[name="bn"]').val();
        var cmdarray = ["_xclick", "_cart", "_oe-gift-certificate", "_xclick-subscriptions", "_xclick-auto-billing", "_xclick-payment-plan", "_donations", "_s-xclick"];
        if(typeof(cmdcode) !== "undefined" && cmdcode !== null) {
            if (cmdarray.indexOf(cmdcode) > -1) {
                if (typeof(bncode) !== "undefined" && bncode.length > 0) {
                    jQuery('input[name="bn"]').val("mbjtechnolabs_SP");
                } else  {
                    jQuery(this).find('input[name="cmd"]').after("<input type='hidden' name='bn' value='mbjtechnolabs_SP' />");
                }
            }
        }
    });
});