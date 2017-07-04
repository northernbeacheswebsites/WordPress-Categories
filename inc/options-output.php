<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

//define all the settings in the plugin
function category_sync_settings_init() { 
    
    //start authorisation section
	register_setting( 'generalSettings', 'category_sync_settings' );
    
	add_settings_section(
		'category_sync_general','', 
		'category_sync_general_callback', 
		'generalSettings'
	);

	add_settings_field( 
		'category_sync_local_server_url','', 
		'category_sync_local_server_url_render', 
		'generalSettings', 
		'category_sync_general' 
	);
    
    add_settings_field( 
		'category_sync_wordpress_user_name','', 
		'category_sync_wordpress_user_name_render', 
		'generalSettings', 
		'category_sync_general' 
	);
    
    add_settings_field( 
		'category_sync_wordpress_user_password','', 
		'category_sync_wordpress_user_password_render', 
		'generalSettings', 
		'category_sync_general' 
	);
    
    
    register_setting('general','update_categories_button');
    
    add_settings_section(
		'category_sync_button','', 
		'category_sync_button_callback', 
		'general'
	);

}

/**
* 
*
*
* The following functions output the callback of the sections
*/
function category_sync_general_callback(){}
function category_sync_button_callback(){
    ?>

    <button id="update-categories-now" class="button button-secondary"><?php _e('Update categories now', 'category-sync' ); ?></button>   

    <?php 
}


//the following functions output the option html
function category_sync_local_server_url_render() { 
	$options = get_option( 'category_sync_settings' );
	?>
    <tr valign="top">
        <td scope="row">
            <label for="category_sync_local_server_url"><?php _e('Local server URL e.g. http://localhost:3000', 'category-sync' ); ?></label>
        </td>
        <td>
            <input type='text' class="regular-text" name='category_sync_settings[category_sync_local_server_url]' id="category_sync_local_server_url" value='<?php if(isset($options['category_sync_local_server_url'])){echo $options['category_sync_local_server_url'];} ?>'>
        </td>
    </tr>
	<?php
}

//the following functions output the option html
function category_sync_wordpress_user_name_render() { 
	$options = get_option( 'category_sync_settings' );
	?>
    <tr valign="top">
        <td scope="row">
            <label for="category_sync_wordpress_user_name"><?php _e('WordPress username', 'category-sync' ); ?></label>
        </td>
        <td>
            <input type='text' class="regular-text" name='category_sync_settings[category_sync_wordpress_user_name]' id="category_sync_wordpress_user_name" value='<?php if(isset($options['category_sync_wordpress_user_name'])){echo $options['category_sync_wordpress_user_name'];} ?>'>
        </td>
    </tr>
	<?php
}

//the following functions output the option html
function category_sync_wordpress_user_password_render() { 
	$options = get_option( 'category_sync_settings' );
	?>
    <tr valign="top">
        <td scope="row">
            <label for="category_sync_wordpress_user_password"><?php _e('WordPress password', 'category-sync' ); ?></label>
        </td>
        <td>
            <input type='text' class="regular-text" name='category_sync_settings[category_sync_wordpress_user_password]' id="category_sync_wordpress_user_password" value='<?php if(isset($options['category_sync_wordpress_user_password'])){echo $options['category_sync_wordpress_user_password'];} ?>'>
        </td>
    </tr>
	<?php
}






?>