<?php 
require_once("EbaySalesListerSale.class.php");

//$x = getAllSales("labolina2",218,10);
////$x = getAllSales("mobileexperten",77,10);
//print_r($x);

/**
 * Creates an URL request for the given sellerID, sideID, and maxEntries.
 * Takes the returned XML Data and creates EbaySalesListerSale objects out of it and
 * returns the in an array.
 * 
 * working example URL:
 * http://open.api.ebay.com/shopping?callname=FindItemsAdvanced&version=631&siteid=0&appid=asdf3e6e3-8b07-4fcf-b4dc-9fb41586455&SellerID=sun-oldsbay&MaxEntries=10&ItemSort= EndTime&ItemType= AllItemTypes&IncludeSelector=SearchDetails&responseencoding=XML
 * 
 * http://developer.ebay.com/DevZone/shopping/docs/CallRef/FindItemsAdvanced.html
 * http://developer.ebay.com/devzone/shopping/docs/Concepts/ShoppingAPI_FormatOverview.html#StandardURLParameters
 * http://developer.ebay.com/devzone/shopping/docs/Concepts/ShoppingAPI_FormatOverview.html#AffiliateURLParameters
 * 
 * @param $sellerID				The name (also called ID) of the ebay seller.
 * @param $siteID				ID of the ebay website from which to return items.
 * @param $maxEntries			How many auctions should be returned at max?
 * @param $trackingid			The ID of the affiliate (sometimes called customer ID.
 * @param $trackingpartnercode	ID for one of the ebay partner programs (valid values are specified in settings.ini) 
 * @return array
 */
function getAllSales($sellerID,$siteID,$maxEntries,$trackingid,$trackingpartnercode) {
	
	// if the sellerID is empty directly return an empty array
	if (empty($sellerID))
		return array();		
		
	$trackingid = trim($trackingid);
		
	// if no trackingid is provided, use mine and EbayPartnerNetwork
	// TODO enter my affiliate ID here
//	if (empty($trackingid)) {
//		$trackingid = 123;
//		$trackingpartnercode = 9;
//	}
	
	// some vars for better readability
    $endpoint = 'http://open.api.ebay.com/shopping';  
    $responseEncoding = 'XML';   
    $version = '631';   // API version number
    $appID   = 'asdf3e6e3-8b07-4fcf-b4dc-9fb41586455'; 
    $itemType  = "AllItemTypes";
    $itemSort  = "EndTime";
    
    // Construct the FindItems call 
    $apicall = "$endpoint?callname=FindItemsAdvanced"
             . "&version=$version"
             . "&siteid=$siteID"
             . "&appid=$appID"
             . "&SellerID=$sellerID"
             . "&MaxEntries=$maxEntries"
             . "&ItemSort=$itemSort"
             . "&ItemType=$itemType"
             . "&IncludeSelector=SearchDetails"             // to get Converted Price
             . "&responseencoding=$responseEncoding";    
             
    // if affiliate information is not empty, add it to the call
    if ( !empty($trackingid) && !empty($trackingpartnercode) ) {
		$apicall .=	"&trackingid=$trackingid"
             		. "&trackingpartnercode=$trackingpartnercode";
//             		. "&affiliateuserid=xyz"  ;	
    }    
    
    // load the call and capture the document returned by the Shopping API
    $resp = @simplexml_load_file($apicall);
    
    // array for storing the EbaySalesListerSale objects
    $sales = array();
    
    // FIX test: changed !empty() with isset()
    // check to see if the response was loaded, else print an error
    if ( isset($resp) && isset($resp->SearchResult->ItemArray) ) {

        // if the response was loaded, parse it and build links  
        foreach($resp->SearchResult->ItemArray->Item as $item) {
        	// get values from response
        	$link  = strval($item->ViewItemURLForNaturalSearch);
        	$endTime = $item->EndTime;
        	$price = sprintf("%01.2f", $item->ConvertedCurrentPrice);
        	$title = strval($item->Title);
        	$picURL = strval($item->GalleryURL);
        	
        	// create new object and add it to array
        	$sales[] = new EbaySalesListerSale($link, $endTime, $price, $title,$picURL);  
        }
    }

     // if there was no search response an empty array will be returned
    return $sales;  
}
?>