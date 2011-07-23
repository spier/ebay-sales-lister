# WordPress Plugin - ebay-sales-lister

This is the source code for my [WordPress](http://wordpress.org) plugin called [ebay-sales-lister](http://wordpress.org/extend/plugins/ebay-sales-lister/). For even more information also see my [blog post](http://blog.airness.de/2007/05/01/wordpress-plugin-ebay-sales-lister/) about the plugin, which contains a lot of feedback from users as well.

WordPress hosts the plugins in SVN. This is the [SVN repo](http://svn.wp-plugins.org/ebay-sales-lister/) for my plugin. This is not really great for community engagement and open source development though. Therefore I decided to move the sources of the plugin to github. I have not worked out yet how I will get changes from here back into SVN but once it gets an issue I will figure it out :)

## How to contribute to this plugin

* you should:
	* fork this repository
	* make your changes
	* send me a pull request
* I will:
	* review your changes
	* merge them with the next release if I consider it appropriate (and give attribution to you in the README if I do so)


## Converting svn repo to git repo

I tried to use `svn2git` for the conversion of the SVN repo at WordPress to a github repository but I could not get that to work. I always got an error message like that. Looks to me like svn2git needs to checkout the whole SVN repo in order to do the conversion, which is pretty much impossible in case of the WP repo because of its size. The error message I got was:

> Using higher level of URL: http://plugins.svn.wordpress.org/ebay-sales-lister/trunk => http://plugins.svn.wordpress.org
