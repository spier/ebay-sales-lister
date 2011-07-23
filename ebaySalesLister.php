<?php
/*
Plugin Name: EbaySalesLister
Plugin URI: http://blog.airness.de/2007/05/01/wordpress-plugin-ebay-sales-lister/
Description: Display a list of ebay auctions in the sidebar.
Version: 0.9
Author: Sebastian Spier
Author URI: http://blog.airness.de/
*/

/*  Copyright 2007-2009 Sebastian Spier (email : blog@airness.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// absolute path to this plugin (used for the .ini files)
@define("EBAYSALESLISTER_ABSPATH", 	dirname(__FILE__) . '/');

// web path to this plugin (for displaying the thumbnail placeholder
@define("EBAYSALESLISTER_WEBPATH",	WP_PLUGIN_URL . strrchr(dirname(__FILE__),'/') . '/');

// constants for the configuration of the title display
// the widget output will display a title while the page output does not need it as it already has a page title
@define("EBAYSALESLISTER_DISPLAY_TITLE_YES", 1);
@define("EBAYSALESLISTER_DISPLAY_TITLE_NO", 0);

// libraries used for the output
require( EBAYSALESLISTER_ABSPATH . "GetAllSalesAPI.php");
require_once( EBAYSALESLISTER_ABSPATH . "EbaySalesListerSale.class.php");

class EbaySalesLister {
	// ID for this application, needed for the ebay API calls
	var $appID   = 'asdf3e6e3-8b07-4fcf-b4dc-9fb41586455'; 
	// version of the ebay shoppin API that is currently used
	var $ebayApiVersion = 631;
	// TODO remove
	var $ebayWorldUrl; // = "http://myworld.ebay.de";
	// array for storing the created sales objects
	var $sales = array();
	// holds language strings for the used language that are used in the output
	var $langStrings = array();
	
	// options from wordpress db table "wp_options";
	var $options;
	
	// holds values from settings.ini
	var $globalSettings;
	
	// constant: maximum number of auctions to retrieve
	var $infinity = 1000;
	
	// option representatives used within this script (so option names within $options could be changed)
	var $ebayUsername;
	var $itemCount;
	var $title;
	var $ebayWebsite;
	var $timeFormat;
	var $language;
	var $linkmode;
	var $trackingid;
	var $trackingpartnercode;

	/**
	 * Creates a new instance of the EbaySalesLister.
	 * @return object	EbaySalesLister
	 */
	function EbaySalesLister() {
		// get options from WordPress DB
		$options = get_option('ebaySalesLister');

		// init options in local instance
		$this->setOptions($options);
		
		$this->globalSettings = parse_ini_file(EBAYSALESLISTER_ABSPATH . "cfg/settings.ini",true);
		$this->ebayWebsitesInformation = $this->globalSettings["ebayWebsitesInformation"];

		// TODO hack, remove it
		// TODO get ebayworld URL from user profile via API call
		$websiteDisplay = $this->globalSettings["ebayWebsitesInformation"][$this->ebayWebsite][0];
		list($country,$website) = split(" - ",$websiteDisplay);
		
		// modify the ebay world URL so that it uses the local ebay website that the user has chosen
		// TODO I should only retrieve the ebay world URL of a user when I need to retrieve it. will save one API call
		$this->ebayWorldUrl = str_replace("ebay.de",$website,$this->ebayWorldUrl);
	}	
	
	/**
	 * Reads the language specific strings from the .ini file
	 * and saves them in the "langStrings" attribute.
	 *
	 * @param string $language	Shortform of the language name
	 */
	function setLangStrings($language){
		$languageStrings = parse_ini_file( EBAYSALESLISTER_ABSPATH . "cfg/languages.ini",true);
		$this->langStrings = $languageStrings[$language];
	}	
	
	/**
	 * Write options from $options[] to local representatives
	 * 
	 * @param array $options 
	 */
	function setOptions($options){
		$this->options = $options;
		$this->ebayUsername = $options['ebayUsername'];
		$this->ebayWebsite = $options['ebayWebsite'];
		$this->itemCount = $options['itemCount'];
		$this->title = $options['title'];
		$this->timeFormat = $options['timeFormat'];
		$this->language = $options['language'];
		$this->linkmode = $options['linkmode'];
		// affiliate options
		$this->trackingid = $options['trackingid'];
		$this->trackingpartnercode = $options['trackingpartnercode'];
		
		// set the langstrings that we will use
		$this->setLangStrings($this->language);
		
		// if itemCount is "all" set it to the local infinity value
		if ($this->itemCount == "all") 
			$this->itemCount = $this->infinity;
	}
	
	/**
	 * Displays the auction data in the WordPress blog.
	 */
	function createSalesDisplay($args,$displayTitle = EBAYSALESLISTER_DISPLAY_TITLE_YES) {	
		
		// get theme specific code
		extract($args);
		
		// start output
		$out .=  $before_widget;
		$out .= "<div class='ebay'>";
		if ($displayTitle == EBAYSALESLISTER_DISPLAY_TITLE_YES)
			$out .= $before_title ."<p class='ebay_title'>" . $this->title . "</p>" . $after_title;
//			$out .= "<h2 class='ebay_title'>" . $this->title . "</h2>"; this worked but does it work for other designs?
//			$out .= "<h2 class='ebay_title'>" . $before_title . $this->title . $after_title . "</h2>";
		$out .= "<ul>";
		
		if (count($this->sales) != 0) {
			// loop over all auctions
			foreach($this->sales as $sale) {
				
				// create image div. This is only inserted if user wants to display images (has set the option to "yes")
				$imageDiv = sprintf(
					"
	    			<div class='ebay_sale_image'>
	    				<a href='%s'><img src='%s' title='%s'/></a>
	    			</div>					
					",
		    		$sale->link,
		    		(!empty($sale->pictureURL)) ? $sale->pictureURL : EBAYSALESLISTER_WEBPATH . $this->globalSettings["defaultPictureURL"],
		    		$sale->title				
				);

					    	//		
				
				// create the list element for one auction
		    	$out .= sprintf(
		    		"
		    		<li class='ebay_sale'>
		    			%s
		    			<div class='ebay_sale_info'>
			    			<a href='%s'>
			    				%s
			    				%s
			    				<br/>	    			
			    				%s
			    				%s<br/>
			    				%s
			    				%s
			    			</div>
		    			%s
		    		</li>
		    		",
		    		($this->options["displayPictures"] == "yes") ? $imageDiv : "",
		    		$sale->link,
		    		$sale->title,
		    		($this->linkmode == "only_title_as_link") ? "</a>" : "",
		    		(
		    			($this->options["labels"] == "Image") 
		    			? ("<img src='" . EBAYSALESLISTER_WEBPATH . "img/clock.png'/>")
		    			: (($this->timeFormat == "end time") ? $this->langStrings[Endet_am] . ":" : $this->langStrings[Endet_in] . ":")
		    		),
		    		$sale->showDate($this->timeFormat,$this->langStrings),
		    		(
		    			($this->options["labels"] == "Image") 
		    			? ("<img src='" . EBAYSALESLISTER_WEBPATH . "img/money.png'/>")
		    			: $this->langStrings[Preis] . ":"
		    		),
		    		$sale->price . " " . $this->globalSettings["ebayWebsitesInformation"][$this->ebayWebsite][1],
		    		($this->linkmode == "all_text_as_link") ? "</a>" : ""
		    	);	   
			}
		}
		// user did provide username but there are nor articles for this username on ebay
		else if(!(empty($this->ebayUsername))) {
			$this->setSellerMyWorldURL();
			$out .= "<li class='ebay_no_sales'>" . $this->langStrings[keineArtikel] . " <a href='$this->ebayWorldUrl'>$this->ebayUsername</a></li>";
		}
		// user did not provide username
		else {
			$out .= "<li class='ebay_no_sales'> Please provide a username in the EbaySalesLister settings! </li>";
		}
		$out .= "</ul>";
		$out .= "</div>";
		$out .= $after_widget;
		
		return $out;
	}
	
	// should only be called when not items for seller exist and MyWorldURL should be displayed
	function setSellerMyWorldURL() {	      
	    $apicall = "http://open.api.ebay.com/shopping?callname=GetUserProfile"
	             . "&version={$this->ebayApiVersion}"
	             . "&siteid={$this->ebayWebsite}"
	             . "&appid={$this->appID}"
	             . "&UserID={$this->ebayUsername}"
	             . "&IncludeSelector=Details"
	             . "&responseencoding=XML";
	             
	    // Load the call and capture the document returned by the Shopping API
	    $resp = simplexml_load_file($apicall);   
	    
	    // set url
	    $this->ebayWorldUrl = $resp->User->MyWorldURL;
	} 	
	
	// register listeners
	function init() {
		// do widget-specific code
		if (function_exists('register_sidebar_widget') ) {   
			// This registers our widget so it appears with the other available
			// widgets and can be dragged and dropped into any active sidebars.
			register_sidebar_widget('Ebay Sales Lister', array(&$this,'widget'));

			// This registers our optional widget control form. Because of this
			// our widget will have a button that reveals a 300x100 pixel form.
			register_widget_control('Ebay Sales Lister', array(&$this,'control_form'), 320, 420);
		} 
		// add normal options page (found at Settigns -> "Ebay Sales Lister")
		add_action('admin_menu', array(&$this,'add_pages'));	
	}
	
	// http://codex.wordpress.org/Plugins/WordPress_Widgets_Api
	function widget($args) {	
		//$options = get_option('ebaySalesLister');
		//$ebaySales = new EbaySalesLister();
		$this->sales = getAllSales($this->ebayUsername,$this->ebayWebsite,$this->itemCount,$this->trackingid,$this->trackingpartnercode);
		$output = $this->createSalesDisplay($args);
		echo $output;
	}
	
	/*
	function widget($args) {
		print_r($args);
		if ($args == null) {
			$args = array(
				before_widget => "<li>",
				before_title => "<h2 class='ebay'>",
				after_title => "</h2><ul>",
				after_widget => "</ul></li>"
			);
		}	
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		$options = get_option('ebaySalesLister');
		$title = $options['title'];

		// These lines generate our output. Widgets can be very complex
		// but as you can see here, they can also be very, very simple.
		echo $before_widget . $before_title . $title . $after_title;
		//$this->display(); //main call to get imonline icon
		//$ebaySales = new EbaySalesLister();
		$this->getSales();
		$this->display();
		echo $after_widget;
	}	
	*/
	
	// show control form
	function control_form() {	
		// get our options
		//$options = get_option('ebaySalesLister');
				
		// set defaults if no $options exist
		if ( !is_array($this->options) ) {
			$this->options = $this->globalSettings["defaultOptions"];
		}
		
		// form was submitted
		if ( $_POST['esl_submit'] ) {
			// get form data
			$this->options['title'] = strip_tags(stripslashes($_POST['esl_title']));
			$this->options['ebayUsername'] = strip_tags(stripslashes($_POST['esl_ebayUsername']));
			$this->options['ebayWebsite'] = strip_tags(stripslashes($_POST['esl_ebayWebsite']));
			$this->options['itemCount'] = $_POST['esl_itemCount'];
			$this->options['timeFormat'] = $_POST['esl_timeFormat'];
			$this->options['language'] = $_POST['esl_language'];
			$this->options['linkmode'] = $_POST['esl_linkmode'];
			$this->options['displayPictures'] = $_POST['esl_displayPictures'];
			$this->options['labels'] = $_POST['esl_labels'];
			// affiliate options
			$this->options['trackingid'] = strip_tags(stripslashes($_POST['esl_trackingid']));
			$this->options['trackingpartnercode'] = $_POST['esl_trackingpartnercode'];
			$this->options['affiliateuserid'] = strip_tags(stripslashes($_POST['esl_affiliateuserid']));
			//$options['affiliCode'] = $_POST['esl_affiliCode'];
					
			// save options
			update_option('ebaySalesLister', $this->options);		
		}
		
		?>
		<div style="">
			<table border="0" cellspacing="0" cellpadding="3" width="100%">
                <tr>
					<td class="ebay_admin_label">
						<label>Title:</label>
					</td>
					<td>
						<input class="ebay_admin_input_textfield" name="esl_title" type="text"
						value="<?php echo $this->options['title']?>" />
					</td>
				</tr>
                <tr>
					<td class="ebay_admin_label">
						<label>Ebay Username:</label>
					</td>
					<td>
						<input class="ebay_admin_input_textfield" name="esl_ebayUsername" type="text"
						value="<?php echo $this->options['ebayUsername']?>" />
					</td>
				</tr>	
                <tr>
					<td class="ebay_admin_label">
						<label>Ebay Website:</label>
					</td>
					<td>
						<select name="esl_ebayWebsite" style="width:150px;">
						<?php					
							foreach($this->globalSettings["ebayWebsitesInformation"] as $siteKey => $siteData) {
								printf(
									"<option value='%d'%s>%s (%s)</option>",
									$siteKey,
									($this->options['ebayWebsite'] == $siteKey) ? " selected" : "",
									$siteData[0],
									$siteData[1]
								);
							}
						?>
						</select>	
					</td>
				</tr>					
				<tr>
					<td class="ebay_admin_label"> 
						<label>Auctions:</label>
					</td>
					<td>
						<select name="esl_itemCount" style="width:150px;">
						<?php
							foreach($this->globalSettings["auctionLimitOptions"] as $count) {
								echo '<option value="' . $count . '"';
								if ($this->options['itemCount'] == $count) echo ' selected="selected"';
								echo '>' . $count . '</option>';
							}
						?>						
						</select>
					</td>
				</tr>	
                <tr>
					<td class="ebay_admin_label">
						<label>Time Format:</label>
					</td>
					<td>
						<select name="esl_timeFormat" style="width:150px;">
						<?php
							foreach(array("end time","time remaining") as $value) {
								echo '<option value="' . $value . '"';
								if ($this->options['timeFormat'] == $value) echo ' selected="selected"';
								echo '>' . $value . '</option>';
							}
						?>	
						</select>
					</td>
				</tr>
                <tr>
					<td class="ebay_admin_label">
						<label>Language:</label>
					</td>
					<td>
						<select name="esl_language"  style="width:150px;">
						<?php
							$languages = $this->globalSettings["languages"];
							
							foreach($languages as $langKey => $langValue) {
								echo '<option value="' . $langKey . '"';
								if ($this->options['language'] == $langKey) echo ' selected="selected"';
								echo '>' . $langValue . '</option>';
							}
						?>
						</select>
					</td>
				</tr>						
			   <tr>
					<td class="ebay_admin_label">
						<label>Link Mode:</label>
					</td>
					<td>
						<select name="esl_linkmode" style="width:150px;">
						<?php							
							foreach($this->globalSettings["linkModes"] as $modeKey => $modeDisplayValue) {
								printf(
									"<option value='%s'%s>%s</a>",
									$modeKey,
									($this->options['linkmode'] == $modeKey) ? " selected='selected'" : "",
									$modeDisplayValue
								);
							}
						?>
						</select>
					</td>
				</tr>	
				<tr>
					<td class="ebay_admin_label"> 
						<label>Display Thumbnails:</label>
					</td>
					<td>
						<select name="esl_displayPictures" style="width:150px;">
						<?php
							foreach(array("yes","no") as $opt) {
								echo '<option value="' . $opt . '"';
								if ($this->options['displayPictures'] == $opt) echo ' selected="selected"';
								echo '>' . $opt . '</option>';
							}
						?>						
						</select>
					</td>
				</tr>	
				<!-- 
				<tr>
					<td class="ebay_admin_label"> 
						<label>Labels:</label>
					</td>
					<td>
						<select name="esl_labels" style="width:150px;">
						<?php
							foreach(array("Text","Image") as $opt) {
								echo '<option value="' . $opt . '"';
								if ($this->options['labels'] == $opt) echo ' selected="selected"';
								echo '>' . $opt . '</option>';
							}
						?>						
						</select>
					</td>
				</tr>
				 -->
                <tr height="70">
					<td colspan="2" class="ebay_admin_label" style="height:20px; text-align: center;">
						<label>Ebay Affiliate Settings</label><br/>
						<span class="ebay_admin_label_description">
							Filling out the section below is optional.<br/> 
							You have to be an <a href="https://ebaypartnernetwork.com" target="_blank">ebay affiliate</a> to use it.
							<!--  
							Leaving it empty or leaving my Tracking ID in here will
							give back some peanuts for <a href="http://wordpress.org/extend/plugins/ebay-sales-lister" target="_blank">my development work</a> of this plugin. So it is not to your disadvantage :-)
							-->
						</span>	
					</td>
				</tr>					 
                <tr>
					<td class="ebay_admin_label">
						<label>Tracking ID:</label>
					</td>
					<td>
						<input class="ebay_admin_input_textfield" name="esl_trackingid" type="text"
						value="<?php echo $this->options['trackingid']?>" />
					</td>
				</tr>	
                <tr>
					<td class="ebay_admin_label">
						<label>Tracking Partner:</label>
					</td>
					<td>
						<select name="esl_trackingpartnercode"  style="width:150px;">
						<?php
							$trackingpartnercodes = $this->globalSettings["trackingpartnercodes"];
							
							foreach($trackingpartnercodes as $code => $name) {
								echo '<option value="' . $code . '"';
								if ($this->options['trackingpartnercode'] == $code) echo ' selected="selected"';
								echo '>' . $name . '</option>';
							}
						?>
						</select>
					</td>
				</tr>	
				<!-- 
                <tr>
					<td class="ebay_admin_label">
						<label>Affiliate User ID:</label>
					</td>
					<td>
						<input class="ebay_admin_input_textfield" name="esl_affiliateuserid" type="text"
						value="<?php echo $this->options['affiliateuserid']?>" />
					</td>
				</tr>		
				 -->								 								
			</table>

			<input type="hidden" name="esl_submit" value="1" />
		</div>
		<?php
	}
	
	// show widget control
	function widget_control() {
		$this->control_form();
	}	
	
	// show standard control
    function standard_control() {
         ?>
		 <div class="wrap">
         <h2>Ebay Sales Lister Options</h2>
         <div style="margin-top:20px;">
		 <form action="<?php echo get_bloginfo('wpurl') ?>/wp-admin/options-general.php?page=ebaySalesLister&updated=true" method="post">
          <?php

               $this->control_form();

         ?>
         <p class="submit"><input type="submit" value="Save changes &raquo;"></p>
         </form></div></div>
		 <?php
        }	
	
    /**
     * Add normal options page for the EbaySalesLister to the AP Admin area
     */
	function add_pages() {
		// add_options_page(page_title, menu_title, access_level/capability, file, [function]);
		add_options_page("Ebay Sales Lister Options", "Ebay Sales Lister", 10, "ebaySalesLister", array(&$this,'standard_control'));
	}
	
	/**
	 * Set the number of auctions that will be retrieved by the ebay API call and then
	 * displayed in the output.
	 * @param $itemCount	number of auctions to be fetched
	 */
	function setItemCount($itemCount) {
		if ($itemCount == "all")
			$this->itemCount = $this->infinity;
		else
			$this->itemCount = $itemCount;
	}
	
	// TODO comment
	function setEbayUsername($ebayUsername) {
		$this->ebayUsername = $ebayUsername;
	}
	
	// TODO comment
	function setEbayWebsite($ebayWebsite) {
		$this->ebayWebsite = $ebayWebsite;
	}
	
	/**
	 * Write link to stylesheet for the EbaySalesLister output that
	 * is displayed within the WP blog.
	 */
	function writeCSSWidget() {
		echo ( '<link rel="stylesheet" type="text/css" href="' . EBAYSALESLISTER_WEBPATH . 'css/style.css">' ); 
	}
	
	/**
	 * Write link to stylesheet for the EbaySalesLister configuration area
	 * that is displayed in the WP admin area
	 */	
	function writeCSSAdmin() {
		echo ( '<link rel="stylesheet" type="text/css" href="' . EBAYSALESLISTER_WEBPATH . 'css/styleAdmin.css">' ); 
	}	
		
}

/**
 * Lists all ebay items for the seller configured in the options form of the widget.
 * This function ignores the "# Auction" setting and just displays all sales.
 * So this function could be used on a static page while the widget function
 * is used in the sidebar.
 * 
 * @param $itemCount how many auctions should be displayed (at max)
 */
function listEbaySales($itemCount = null) {
	// create new EbaySalesLister
	$esl = new EbaySalesLister();
	
	// if an itemCount is given, use this one instead of the one configured in the options
	if (!empty($itemCount))
		$esl->setItemCount($itemCount);
	
	// get the ebay auctions
	$esl->sales = getAllSales($esl->ebayUsername,$esl->ebayWebsite,$esl->itemCount,$esl->trackingid,$esl->trackingpartnercode);
	$auctionOutput = $esl->createSalesDisplay(array(),EBAYSALESLISTER_DISPLAY_TITLE_NO);
	
	// remove the linebreaks (as they are replaced with <br/> in the output) 
	$auctionOutput = preg_replace("/(\r\n)|\n|\r/","",$auctionOutput);
	
	// print everything to screen
	echo $auctionOutput;
}

function createEbaySalesOutput($itemCount = null) {
	// create new EbaySalesLister
	$esl = new EbaySalesLister();
	
	// if an itemCount is given, use this one instead of the one configured in the options
	if (!empty($itemCount))
		$esl->setItemCount($itemCount);
	
	// get the ebay auctions
	$esl->sales = getAllSales($esl->ebayUsername,$esl->ebayWebsite,$esl->itemCount,$esl->trackingid,$esl->trackingpartnercode);
	$auctionOutput = $esl->createSalesDisplay(array(),EBAYSALESLISTER_DISPLAY_TITLE_NO);
	
	// remove the linebreaks (as they are replaced with <br/> in the output) 
	$auctionOutput = preg_replace("/(\r\n)|\n|\r/","",$auctionOutput);
	
	// print everything to screen
	return $auctionOutput;
}

// main routine
if (class_exists("EbaySalesLister")) {
	// create Instance for EbaySalesLister and set the configuration options
	$EbaySalesLister = new EbaySalesLister();
	// run our code later in case this loads prior to any required plugins.
	add_action('plugins_loaded', array(&$EbaySalesLister,'init'));
	// add stylesheet to WP header
	add_action('wp_head', array(&$EbaySalesLister,'writeCSSWidget'));
	// add stylesheet to WP Admin header
	add_action('admin_head', array(&$EbaySalesLister,'writeCSSAdmin'));
}

?>