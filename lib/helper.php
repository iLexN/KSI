<?php
/**
 * download.php use to check the working hour download time.
 * @return boolean
 */
function isWorkingHour(){
    $d = date('w');
    if ( $d == 6 || $d == 7 ) {
        return false;
    }
    $h = date("G");
    if ( $h >= 9 && $h < 18) {
        return true;
    }

    return false;
}

