<?php

/**
 * Class representing one single ebay sale
 */
class EbaySalesListerSale {
	// auction title
	var $title;
	// end time of auction (dd.mm.yy hh:mm:ss)
	var $endTime;
	// end time of auction as unix timestamp
	var $endTimeTimestamp;
	// price of this auction (also contains the currency)
	var $price;
	// absolute link to the auction page
	var $link;
	// picture for this auction
	var $pictureURL;
	
	/**
	 * Creates a new ebay sale item
	 *
	 * @param string $link
	 * @param string $endTime
	 * @param string $price
	 * @param string $title
	 * @return EbaySalesListerSale
	 */
	function EbaySalesListerSale($link, $endTime, $price, $title, $pictureURL) {
		$this->title = $title;
		$this->endTimeTimestamp = strtotime($endTime);   // returns Epoch seconds$this->createTimestamp($endTime);
		$this->endTime = date("d.m.y H:i:s",$this->endTimeTimestamp);
		$this->price = $price;
		$this->link = $link;
		$this->pictureURL = $pictureURL;
	}
	
	/**
	 * Checks if this object represents a valid ebay sale (data is correct).
	 * @return boolean
	 */
	function isValid(){
		return ($this->endTimeTimestamp != "");
	}
	
	/**
	 * Function used to display the time of this auction
	 * Depending on the $timeFormat option that is set, different results are returned.
	 * @param $timeFormat
	 * @param $langStrings
	 */
	function showDate($timeFormat,$langStrings) {
		if ($timeFormat == "end time") {
			return $this->endTime;
		}
		else if ($timeFormat == "time remaining") {
			return $this->timeRemaining($langStrings);
		}
		else {
			return "Invalid format.";
		}
	}
	
	/**
	 * Returns the remaining time for this ebay sale.
	 * @param array	$langStrings language strings to use for the output
	 */
	function timeRemaining($langStrings) {
		$now = date('Y-m-d H:i:s', time());
		$endTime = date('Y-m-d H:i:s', $this->endTimeTimestamp);
		$diff = $this->get_time_difference($now,$endTime);
						
		$remaining = "";
		if ($diff[days] > 0) {
			$remaining .= "$diff[days] " . (($diff[days] == 1) ? $langStrings[Tag] : $langStrings[Tage]);
			$remaining .= " $diff[hours] " . (($diff[hours] == 1) ? $langStrings[Stunde] : $langStrings[Stunden]);
		}
		else if ($diff[hours] > 0){
			$remaining .= "$diff[hours] " . (($diff[hours] == 1) ? $langStrings[Stunde] : $langStrings[Stunden]);
			$remaining .= " $diff[minutes] " . (($diff[minutes] == 1) ? $langStrings[Minute] : $langStrings[Minuten]);
		}	
		else if ($diff[minutes] > 0){
			$remaining .= "$diff[minutes] " . (($diff[minutes] == 1) ? $langStrings[Minute] : $langStrings[Minuten]);
			$remaining .= " $diff[seconds] " . (($diff[seconds] == 1) ? $langStrings[Sekunde] : $langStrings[Sekunden]);
		}	
		else if ($diff[seconds] > 0){
			$remaining .= "$diff[seconds] " . (($diff[seconds] == 1) ? $langStrings[Sekunde] : $langStrings[Sekunden]);
		}	
		else if(diff == false) {
			$remaining .= "Time calculation error";
		}
		else {
			$remaining .= "Auction ended";
		}	

		return $remaining;
	}
	
	/**
	 * Function to calculate date or time difference.	 
	 *
	 * Function to calculate date or time difference. Returns an array or
	 * false on error.
	 *
	 * @author       J de Silva                             <giddomains@gmail.com>
	 * @copyright    Copyright &copy; 2005, J de Silva
	 * @link         http://www.gidnetwork.com/b-16.html    Get the date / time difference with PHP
	 * @param        string                                 $start
	 * @param        string                                 $end
	 * @return       array
	 */
	function get_time_difference( $start, $end ) {
	    $uts['start']      =    strtotime( $start );
	    $uts['end']        =    strtotime( $end );
	    if( $uts['start']!==-1 && $uts['end']!==-1 )
	    {
	        if( $uts['end'] >= $uts['start'] )
	        {
	            $diff    =    $uts['end'] - $uts['start'];
	            if( $days=intval((floor($diff/86400))) )
	                $diff = $diff % 86400;
	            if( $hours=intval((floor($diff/3600))) )
	                $diff = $diff % 3600;
	            if( $minutes=intval((floor($diff/60))) )
	                $diff = $diff % 60;
	            $diff    =    intval( $diff );            
	            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
	        }
	    }
	    return( false );
	}	
	
}
?>