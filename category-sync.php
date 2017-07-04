<?php

/*
*		Plugin Name: Category Sync
*		Description: Syncs WordPress Categories with a Local Server
*		Version: 1.0
*		Author: Martin Gibson
*		Text Domain: category-sync
*		Licence: GPL2
*/

//lets add an admin menu page
add_action( 'admin_menu', 'category_sync_add_admin_menu' );
//lets call our setting function
add_action( 'admin_init', 'category_sync_settings_init' );


function category_sync_add_admin_menu() { 
    global $my_custom_post_type_settings_page;
    
	$category_sync_settings_page = add_menu_page( 'Category Sync', 'Category Sync', 'manage_options', 'category_sync', 'category_sync_options_page','');
}


//this function outputs the menu page
function category_sync_options_page() { 
    require('inc/options-page-wrapper.php');
}


//gets, sets and renders options
require('inc/options-output.php');


//function to prevent all users editing categories
function category_sync_prevent_category_editing(){
	$capabilitiesToDelete = array('manage_categories');
	global $wp_roles;
	foreach ($capabilitiesToDelete as $capability) {
		foreach (array_keys($wp_roles->roles) as $role) {
			$wp_roles->remove_cap($role, $capability);
		}
	}
}
add_action( 'admin_init', 'category_sync_prevent_category_editing' );


//function to allow all users editing categories
//uncomment to restore category editing functionality
//function category_sync_allow_category_editing(){
//	$capabilitiesToAllow = array('manage_categories');
//	global $wp_roles;
//	foreach ($capabilitiesToAllow as $capability) {
//		foreach (array_keys($wp_roles->roles) as $role) {
//			$wp_roles->add_cap($role, $capability);
//		}
//	}
//}
//add_action( 'admin_init', 'category_sync_allow_category_editing' );


//this is our main function
function category_sync_update_categories(){
    
    //lets get our main options
    $options = get_option( 'category_sync_settings' );
    
    $wordPressUserName = $options['category_sync_wordpress_user_name'];
    $wordPressPassword = $options['category_sync_wordpress_user_password'];
    
    //first we need to get all categories from our local server
    $response = wp_remote_get($options['category_sync_local_server_url'].'/categories/');

    if ( ! is_wp_error( $response ) ) {
        if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
            // Do something with the response
            $body = wp_remote_retrieve_body( $response );
            $externalServerData = json_decode(preg_replace('/("\w+"):(\d+(\.\d+)?)/', '\\1:"\\2"', $body), true);
        } else {
            $error_message = wp_remote_retrieve_response_message( $response );
        }
    } else {
        $error_message = $response->get_error_message();
    }
    
    
    //then we need to get all categories from WordPress
    
    $siteURL = get_site_url();
    
    $response = wp_remote_get($siteURL.'/wp-json/wp/v2/categories');

    if ( ! is_wp_error( $response ) ) {
        if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
            // Do something with the response
            $body = wp_remote_retrieve_body( $response );
            $wordpressServerData = json_decode(preg_replace('/("\w+"):(\d+(\.\d+)?)/', '\\1:"\\2"', $body), true);
        } else {
            $error_message = wp_remote_retrieve_response_message( $response );
        }
    } else {
        $error_message = $response->get_error_message();
    }
    
    //lets create 2 arrays which will store our category names
    //normally you would use the ID as the primary identifier however because WordPress doesn't allow you to set the ID - well at least through the API we are going to use the name as the primary identifier
    $externalServerCategoryNames = array();
    $externalServerCategoryNamesAndId = array();
    
    foreach ($externalServerData as $name) {
        $categoryName =  $name['name'];
        $categoryId =  $name['id'];
        array_push($externalServerCategoryNames,$categoryName);
        $externalServerCategoryNamesAndId += array($categoryId => $categoryName); 
    }
    
    $wordpressServerCategoryNames = array();
    $wordpressServerCategoryNamesAndId = array();
    $wordpressServerCategoryIDAndParents = array();
    
    foreach ($wordpressServerData as $name) {
        $categoryName =  $name['name'];
        $categoryId =  $name['id'];
        $categoryParent =  $name['parent'];
        array_push($wordpressServerCategoryNames,$categoryName);
        $wordpressServerCategoryNamesAndId += array($categoryName => $categoryId);
        $wordpressServerCategoryIDAndParents += array($categoryId => $categoryParent);
    }
    
    //just for feedback lets document how many times a category is updated, deleted or created in WordPress
    $categoriesUpdatedCount = 0;
    $categoriesDeletedCount = 0;
    $categoriesCreatedCount = 0;
    
    //here we are going to loop through all categories in the external server and if they already exist in WordPress we need to detect if any data is different and if it is, update the category. If the category isn't in WordPress we need to create the category
    foreach ($externalServerData as $item) {
        
        $categoryId = $item['id'];
        $categoryName = $item['name'];
        $categoryParent = $item['parent_id'];
        
        //we need to get the WordPress category parent ID
        if($categoryParent == null) {
            $wordpressCategoryParentID = '';       
        } else {
            $categoryParentName = $externalServerCategoryNamesAndId[$categoryParent];
            $wordpressCategoryParentID = $wordpressServerCategoryNamesAndId[$categoryParentName];
            $wordpressCategoryParentIDWithParameter = '&parent='.$wordpressServerCategoryNamesAndId[$categoryParentName];
        }
        
        if (in_array($categoryName, $wordpressServerCategoryNames)){
            //the category already exists in WordPress so now we need to check if its different and if so update it
            
            $categoryName = $externalServerCategoryNamesAndId[$categoryId];
            $wordpressCategoryID = $wordpressServerCategoryNamesAndId[$categoryName];
            $wordpressCategoryIDParent = $wordpressServerCategoryIDAndParents[$wordpressCategoryID];
            
            
            if($wordpressCategoryParentID == ''){
                $wordpressCategoryParentID = 0;    
            }
            
            
            if(intval($wordpressCategoryParentID) !== intval($wordpressCategoryIDParent)) {
            
                $response = wp_remote_post( $siteURL.'/wp-json/wp/v2/categories/'.$wordpressCategoryID.'?parent='.$wordpressCategoryParentID, array(
                    'headers' => array(
                        'Authorization' => 'Basic '.base64_encode($wordPressUserName.':'.$wordPressPassword),
                    ),
                ) );

                if ( ! is_wp_error( $response ) ) {
                    if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
                        $categoriesUpdatedCount++; 
                    } else {
                        $error_message = wp_remote_retrieve_response_message( $response );
                    }
                } else {
                    $error_message = $response->get_error_message();
                }
            }
            
 
        } else {
            
            //the category isn't in WordPress so we need to create it
            $response = wp_remote_post( $siteURL.'/wp-json/wp/v2/categories?name='.$categoryName.$wordpressCategoryParentIDWithParameter, array(
                'headers' => array(
                    'Authorization' => 'Basic '.base64_encode($wordPressUserName.':'.$wordPressPassword),
                ),
            ) );

            if ( ! is_wp_error( $response ) ) {
                if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
                } else {
                    $error_message = wp_remote_retrieve_response_message( $response );
                }
            } else {
                $error_message = $response->get_error_message();
            }
            $categoriesCreatedCount++;
 
        }
    }
    
    
    //here we are going to loop through all the WordPress items and if theres an item that doesn't exist in the external server we need to delete it
    foreach ($wordpressServerData as $item) {
        
        $categoryName =  $item['name'];
        $categoryId =  $item['id'];
        
        if (in_array($categoryName, $externalServerCategoryNames) || $categoryName == 'Uncategorized'){
            //the wordpress category exists in the external server so nothing needs to be done    
            
        } else {
            //the category is in wordpress and not in the server so lets delete the category
            $response = wp_remote_request( $siteURL.'/wp-json/wp/v2/categories/'.$categoryId.'?force=true', array(
                'method' => 'DELETE',
                'headers' => array(
                    'Authorization' => 'Basic '.base64_encode($wordPressUserName.':'.$wordPressPassword),
                ),
            ) );

            if ( ! is_wp_error( $response ) ) {
                if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
                    $categoriesDeletedCount++;
                } else {
                    $error_message = wp_remote_retrieve_response_message( $response );
                }
            } else {
                $error_message = $response->get_error_message();
            }
 
        }
        
    }
    
    echo $categoriesUpdatedCount.' categories were updated. '.$categoriesCreatedCount.' categories were created. '.$categoriesDeletedCount.' categories were deleted.';
    
    die();  
    
}
//add_action( 'admin_init', 'category_sync_update_categories' );
add_action( 'wp_ajax_update_categories', 'category_sync_update_categories' );


////lets run our function every 30 minutes
function category_sync_recurrence_interval($schedules) {
 
    $schedules['every_thirty_minutes'] = array(
            'interval'  => 30*60,
            'display'   => __( 'Every 30 Minutes', 'category-sync' )
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'category_sync_recurrence_interval' );

//lets run the schedule
if ( ! wp_next_scheduled( 'category_sync_update_categories' ) ) {
    wp_schedule_event( time(), 'every_thirty_minutes', 'category_sync_update_categories' );
}


//lets load a script which will run an ajax call to the function
function category_sync_register_admin_scripts($hook) {
    
    global $pagenow;
    
    if('options-general.php' == $pagenow){
        wp_enqueue_script( 'update-categories', plugins_url( '/inc/update-categories.js', __FILE__ ), array('jquery'));    
    }

}
add_action( 'admin_enqueue_scripts', 'category_sync_register_admin_scripts' );
?>