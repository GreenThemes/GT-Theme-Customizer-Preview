GT-Theme-Customizer-Preview
===================

** Working on Wordpress 4.3.1 as of 12/4/2015 **  

The idea is for Guests to be able to "Try Out" customizing your wordpress theme's options without registering an account.

Try it for yourself, live demo on http://GreenThe.me

** This should be ready for production use, on a demo site. **

Usage:

Use the following Shortcode to create the Guest Login Link.

[GTCustomizer]Click here to Preview this theme's Customizer[/GTCustomizer]

Visit that page, and try out the link! (While not logged in, like a real guest or visitor would)

How it works:
A test user is automatically created the first time you visit the customizer (username="Guest")
Clicking on the link, log them in as the "Guest" user with access to a "pseduo" wordpress live customizer.
The Dashboard, and WP_Nav bar (wp-admin) are blocked to Guest Users. 
A Menu Item is added to the admin bar so they can also use the customizer previewer.

Future Features?
1. Add a theme selector to the customizer.
2. Make some options for changing the default test account's username, and a list of available themes to choose from.



Multi-site Note:
On east subsite you want the Guest user to be able to work, 
be sure "Guest" user has the Preview Themes role added.


Feature Requests and saying thanks:

Please donate any amount. As long as I see something, it will encourage me to spend my focus in that area.

http://greenthe.me/donate/ 


** Changelog **

1.5
Updated the customizer to work with Wordpress 4.3.1
Checked that uploads,saving changes, and uploading anything is blocked. (No permission by Wordpress)

1.4.1
Updated the customizer to work with Wordpress 4.1
Fixed the Customizer Preview Toolbar Link

1.04

Updated includes/gt-customizer.php with new Wordpress 3.6 core javascript libraries
Now requires WP 3.6


1.03

Removed injected styles to remove wpadmin bar fully via functions only
Moved up admin redirect to a faster hook and is_admin check
Added checking if a "Guest" account already exists on another site and adds the existing user to the current demo site automatically and assigns the roles as needed
Made the "wp-blog-header.php" require crawl up a few directories looking for it first. Hopefully this will make it work across more different wordpress configurations.

** TODO **
/includes/gt-customizer.php still has a "hard" path set to the wp-admin. This is the same way the offical wordpress customizer works, but since we are in the plugin folder instead of in wp-admin already, we have to search for the admin.php 
In otherwords, if your plugin's are in a strange location relative to wordpress, you'll need to edit that line at the top to suite your needs.


