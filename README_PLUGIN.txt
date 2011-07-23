=== Ebay Sales Lister ===

Contributors: bbjeff2
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4155680
Tags: auction, auctions, ebay, ebay sales, ebay articles, ebay auctions, links, sidebar, widget
Requires at least: 2.0
Tested up to: 3.0.4
Stable tag: 0.9

The Ebay Sales Lister is a plugin that lets you display a list of ebay sales in the sidebar of your WordPress blog.

== Description ==

The Ebay Sales Lister is a WordPress plugin that lets you display a list of ebay auctions/sales on your WordPress blog.
Your ebay auctions will be presented with their title, the remaining time and the current bid in the sidebar of your blog.
Therefore you will need to use a WordPress theme that provides you with a sidebar. (Note: You can of course display the ebay auctions for any ebay username, so it does not necessarily have to be yours.)

*Contact:* blog@airness.de
*Website:* http://blog.airness.de/2007/05/01/wordpress-plugin-ebay-sales-lister/

== Installation ==

1. Download the plugin zip file (you've likely already done this)
2. Extract it in the `/wp-content/plugins/` directory of your Wordpress installation. Afterward, you should have a folder structure like this: `wp-content/plugins/ebay-sales-lister/`.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Configure the plugin:
	* 	With Sidebar Widgets:
		Just drag & drop the Ebay Sales Lister to your sidebar and fill out the configuration options.
	*	Without Sidebar Widgets:
		Go to Settings -> "Ebay Sales Lister" and configure the plugin.
		Add this PHP function call to the place (probably in your sidebar) where you want the ebay auctions to be displayed: `<?php listEbaySales(); ?>`
	*	As a single page instead of the sidebar:
		Make sure you have the EXEC_PHP plugin installed.
		Create a page and add this call to `<?php listEbaySales("all") ?>` to it.
5. Check your blog to see if it works.

*Configuration Options*

* Title:
	The title to be displayed above the ebay auctions in the sidebar.
* Ebay Username:
	Your ebay login/username, e.g. "airness_de" in my case.
	You can actually enter multiple ebay usernames here, separated by comma.
	So "seller1,seller2,seller3" will display auctions for all three sellers.
	Caution: Do not add spaces between the usernames, so "username1 , username2" will not work!
	Caution: When using multiple usernames the results will still be displayed in chronological order so items of different usernames might be displayed in a mixed order.
	Caution: As far as I know all users have to have there items listend on the "Ebay Website" listed below in order for this to work with multiple usernames.
* Ebay Website:
	The ebay website on which your auctions are listed.
	This value is used to create the links to your articles so make sure you provide the correct value here.
	Please contact me if the ebay website that your are using is not listed here, so that I can add it.
* Auctions:
	The number of auctions you want to display.
	The plugin will of course never display more articles than you have actually running on ebay.
	You can select one of the options "all","10","5" or "3".
	NOTE: Be careful with using option "all" as this might slow down your blog a lot if you list a
	lot of articles at ebay.
* Time Format:
	Choose if you want to display the time the item ends on (option: "end time") or
	the remaining time for this article (option: "time remaining").
* Language:
	Choose the language to be displayed. 
	NOTE: If you would like to add any new language just contact me and provide me with the needed words.
* Link Mode:
	You can choose which part of the ebay auction information in the sidebar should be used as link, that will
	bring the user the to respective ebay auction. The available auctions are "Only Title" and "All Text".
* Dispay Thumbnails:
	Here you can choose whether you want to display thumbnails or not. 
* Tracking ID:  
	Your ID with the tracking partner e.g. your eBay affiliate ID with the eBay Partner Network
* Tracking Partner:
	The tracking partner you are using e.g. eBay Partner Network
	
== Changelog ==

= 0.9 (not yet released) =
* updated readme.txt format
* plugin successfully tested with WP 2.8.6, WP 3.0.4
= 0.8 (2009-10-11) =
* added language support for Hungarian (thx to Aniko)
* switched to ebay API version 631
* a user (thx to kypexin) pointed out that the plugin has one undocumented option which however may be very useful: 
	the ebay API allows to list more that 1 seller within a single query, so you may specify more than 1 seller name in the 
	plugin settings, comma-separated like: "seller1,seller2,seller3". In this case the plugin will show items from all three sellers at once!
* added language support for Spanish (thx to [scwireless](http://scwireless.no-ip.info/charli/))
= 0.7 (2009-06-27) =
* added ability to configure affiliate ID (aka Tracking ID) and affiliate network (aka Tracking Partner) 
= 0.6 (2009-04-28) =	
* all auction information now is retrieved by using the ebay API now (should be faster)
* auction thumbnails now can be displayed together with the auction information
* added option to turn thumbnail display on/off 
* added thumbnail placeholder for auctions that don't have an image
* added CSS class ebay_no_sales that can be used for styling the text that is displayed when the user has no actions
* reimplemented the whole CSS styling of the output and externalized the style to css/style.css
* externalized language strings to cfg/languages.ini
* externalized settings to cfg/settings.ini
* added all ebay websites to the configuration screen (Canada, Australia, ...)
* added language support for Bulgarian (thx to Goga)
* added language support for Polish (thx to Wojtek)
* added language support for Russian (thx to Leo)
* added a function that let's you display all ebay auctions on a page. To do so just include <?php listEbaySales("all") ?> in any page.
	 (You will need the EXEC_PHP plugin for WordPress)
= 0.5 (2009-03-15) =
* Added Italian language support (thx to lorenzocoffee)	
* Fixed problem with wrong ebay shop URL
= 0.4 (2008-12-09) =
* This release is mainly based on modifications proposed and made by [metsuke](http://blog.metsuke.com/?p=752) including the following features
* Added Spanish ebay site (ebay.es)
* Added option to choose which part of the displayed text should be used as a link to the ebay auction. You can now choose to use only the title as a link or the whole text.
* Added CSS classes:
	* All list elements now have their own CSS class (`<li class='ebay_sale'>`)
	* The information other than the title now is surrounded by a span to make it customizable (`<span class='ebay_sale_info'>`)
= 0.3 (2008-09-02) =
* Added Swedish language support (Thanks to [vintagemaniac.net](http://www.vintagemaniac.net/blogg/main/))
* New option for choosing you local ebay website. (eg ebay.com)
* Note: This value is not used for extraction of the auctions from the ebay website but only for generating the correct links.
* Note: This release was more or less ready since 2007-07-12 but I did not release it in between due to a lack of time.	
= 0.2 (2007-05-12) =
* Time calculation error fixed, start time earlier than end time (ll. 598)
* Added Turkish language support	
= 0.1 (2007-05-01) =	
* Basic functionality	
	


== Frequently Asked Questions ==

= It seems like your plugin slows down my blog. =

Yes, it does. Every time the plugin is displayed, it has to download a site from ebay to extract the sales. Thats why your site loads a little bit slower.

= No articles are displayed although I currently have articles running at ebay. =

Please report your Ebay Username and plugin configuration settings. It is probably a problem with a different layout of you local ebay website.

= I see obscure characters in my article titles. =

This is probably an encoding problem. Feel free to send me an email with your plugin configuration settings and I will try to see how I can help you.

== Screenshots ==

1. This is the configuration section for the plugin. Please find explanations for all configuration options in the installation section.
2. Output of the ebay auctions in the sidebar without thumbnails.
3. Output of the ebay auctions in the sidebar with thumbnails.

== Contributors ==

get\_time\_difference function,	J de Silva, [www.gidnetwork.com](http://www.gidnetwork.com/b-16.html)

cURL library, Marcin Juszkiewicz, [www.hrw.one.pl](http://www.hrw.one.pl/)

Modifications for Version 0.4, Raul Carrillo, [metsuke](http://blog.metsuke.com/?p=752)	
