jQuery(document).ready(function ($) {
    
    
    $('#update-categories-now').click(function (event) {
        event.preventDefault(); 
        
        //remove trailing loading message
        $('.settings-loading-message').remove();

        //show loading message
        $('<div class="notice notice-warning is-dismissible settings-loading-message"><p>Please wait while we update the categories...</p></div>').insertAfter('#update-categories-now');
        
        var data = {
                'action': 'update_categories',
            };

            jQuery.post(ajaxurl, data, function (response) {
                
                $('.settings-loading-message').remove();
                
                $('<div class="notice notice-success is-dismissible settings-saved-message"><p>'+response+'</p></div>').insertAfter('#update-categories-now');
            
                setTimeout(function() {
                    $('.settings-saved-message').slideUp();
                }, 10000);
                
            });

    });
    
});