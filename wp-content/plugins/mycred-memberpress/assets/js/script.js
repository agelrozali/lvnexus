jQuery(document).ready(function () {

    jQuery(document).on( 'click', '.mycred-add-memberpress-hook', function(event) {
        var hook = jQuery(this).closest('.hook-instance').clone();
        hook.find('input.mycred-memberpress-creds');
        hook.find('input.mycred-memberpress-log');
        hook.find('select.mycred_memberpress_product');
        jQuery(this).closest('.hook-instance').after( hook );       
    }); 

    jQuery(document).on( 'click', '.mycred-remove-memberpress-hook', function() {    
        var container = jQuery(this).closest('.checking');
        if ( container.find('.hook-instance').length > 1 ) {
            var dialog = confirm("Are you sure you want to remove this hook?");
            if (dialog == true) {
                jQuery(this).closest('.hook-instance').remove();
            } 
        }
    });

    jQuery(document).on( 'click', '.mycred-recurring-check', function() {
        
        var val = jQuery(this).is(':checked');

        if( val == true ){
            jQuery('.recurring-check').css( "display" , "block" ); 
        }else{
            jQuery('.recurring-check').css( "display" , "none" );
        } 

    });

    jQuery(document).on( 'click', '.mycred-specific-recurring-check', function() {
        
        var val = jQuery(this).is(':checked');
        var container = jQuery(this).closest('.hook-instance').find('.specific-recurring-check');
        var hiden_field = jQuery(this).closest('.hook-instance').find('.specific-product-recurring');

        if( val == true ){
            hiden_field.val('1');
            container.css( "display" , "block" ); 
        }else{
            hiden_field.val('0');
            container.css( "display" , "none" );
        } 

    });
});