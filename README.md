# WordPress-Categories

### Setup notes
This WordPress plugin called 'category-sync' syncs categories between a WordPress installation and an external database with the external database being the master and the WordPress database being the slave. For testing purposes a JSON server was used: https://github.com/typicode/json-server

To install the plugin, drag and drop the plugin into your wp-content folder, and activate the plugin. 

I took the unorthodox and perhaps less practical method of updating the categories by using the WordPress Rest API to update the categories. A faster and easier method would have been to use the wp_update_term() function: https://developer.wordpress.org/reference/functions/wp_update_term/ however I wanted to demonstrate my familiarity with the WordPress Rest API so I took the more challenging path. So therefore the plugin has 2 plugin dependencies: 

1. WordPress Rest API: https://wordpress.org/plugins/rest-api/ - enables Rest API functionality
2. Basic Authentication Handler: https://github.com/WP-API/Basic-Auth - which enables basic authentication to WordPress API calls

Upon activating the plugin you'll see a new main menu item called 'Category Sync'. Please click on this and you'll see 3 settings:

1. Local Server URL - where you can enter in the URL of the local server to sync with e.g. http://localhost:3000 - please note for this exercise I have already appended a suffix to the local server URL so by entering http://localhost:3000 it will make calls to: http://localhost:3000/categories/ so your db.json file can use the data as provided in the exercise. 
2. Your WordPress username - e.g. admin or your name
3. Your WordPress password

Once these details have been entered successfully you can now go to the Settings > General page and you'll see a new button called 'Update categories now'. By pressing on this button it will update the WordPress categories and it will even give you feedback as to how many categories were updated, deleted or created from the function. It would be more practical to have this button on this 'Category Sync' menu page but I thought I should follow the guidelines and put it on the Settings > General page. 

I have also setup a WordPress Cron which will run the function every 30 minutes. Obviously the WordPress Cron isn't a real cron so the website needs to be hit for the cron to be activated. There are more legit solutions including this project on Github which could run the function like a real cron: https://github.com/humanmade/Cavalcade.

I also completed bonus item number 2 as upon activating the plugin no user role will have the ability to add new categories. Sometimes even when you deactivate the plugin this permission change can still stay in effect which can be annoying, therefore I have created a commented out function in the category-sync.php file which will re-enable this function - you would obviously need to comment out the category_sync_prevent_category_editing() function. If I had more time I would have just made a setting for this.

The way the WordPress username and password are stored in the settings is not ideal and there are better ways to manage this information but for this exercise I just wanted to get things up and running easily. As I mentioned before it is unorthodox for WordPress to be making calls to itself but this solution would be well suited to a headless WordPress setup.

Whilst this solution works, in the real world there would be posts associated with these categories. The problem with this solution is that the WordPress API doesn't provide ability to update a categories ID. Therefore I had to use the categories name as the primary identifier of the category when ideally I would have used the ID. So because of this I had to come up with some code which would find the existing category based off the name of the category. What this means is if a categories name changes on the JSON server (and even if its ID stays the same), the plugin will add a new category to WordPress. Also part of the function is that it deletes excess categories in WordPress so the 2 category structures are identical. So the old category would be deleted. What this would mean is any posts assigned to that category will no longer be assigned to that category and they would need to be re-associated with the new category created. This could be overcome by storing the original category ID as meta information of the category, but hey that could be version 2 of the plugin! But with that said the plugin will update the parent ID's appropriately so if a parent ID changes the plugin won't delete and recreate anything it will actually just update the parent ID of the existing category. So I hope this demonstrates my ability to run the update routine. But strictly speaking the plugin does produce the result of keeping the categories in sync.  

### What I thought of the exercise
I thought it was good! No further comments :)

### How long it took to complete the exercise
Around 4-5 hours
