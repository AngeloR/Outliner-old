<?php 
/* 
 * JavaScript Pretty Date 
 * Copyright (c) 2008 John Resig (jquery.com) 
 * Licensed under the MIT license. 
 */ 

// Ported to PHP >= 5.1 by Zach Leatherman (zachleat.com) 
// Slight modification denoted below to handle months and years. 
// Edited so that you pass in time-stamps and fixed the bug where JS timestamps 
// don't match exactly with PHP ones. PHP uses seconds, not micro seconds. 
class Date_Difference 
{ 
	/**
	 * 
	 * Pass in a timestamp and it tells you the relative date
	 * @param int $date The timestamp for the date
	 */
    public static function getString($date) 
    { 
    		
        $diff = floor((date('U') - $date)); 
        $dayDiff = floor($diff/86400);


        
        if($dayDiff == 0) { 
            if($diff < 60) { 
                return 'Just now'; 
            } elseif($diff < 120) { 
                return '1 minute ago'; 
            } elseif($diff < 3600) { 
                return floor($diff/60) . ' minutes ago'; 
            } elseif($diff < 7200) { 
                return '1 hour ago'; 
            } elseif($diff < 86400) { 
                return floor($diff/3600) . ' hours ago'; 
            } 
        } elseif($dayDiff == 1) { 
            return 'Yesterday'; 
        } elseif($dayDiff < 7) { 
            return $dayDiff . ' days ago'; 
        } elseif($dayDiff == 7) { 
            return '1 week ago'; 
        } else { 
            // return the date that it was modified
            return date('F n, Y', $date); 
        } 
    } 
}