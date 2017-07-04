<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
$siteURL = get_site_url();
?>

<div class="wrap">
    <div id="poststuff">
        
        <h1><?php _e('General Settings', 'category-sync' ); ?></h1>
        
        <form id='category_sync_settings_form' action='options.php' method='post'>
            <table class="form-table">

                <!--fields-->
                <?php
                settings_fields('generalSettings');
                do_settings_sections('generalSettings');
                ?>
                    
                <button type="submit" name="submit" id="submit" class="button button-primary">
                <?php _e('Save All Settings', 'category-sync' ); ?></button>    
                </table> 
            
        </form>    
        
    </div>
</div>