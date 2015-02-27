<?php

namespace Ksi;

use ORM as ORM;

class User {

    public function __construct() {
        
    }

    /**
     * Find the sales list from KSI , order by asc
     * 
     * @return array array(userName1,uesrName2 ...)
     */
    public static function salesList(){
        $user = ORM::for_table('ppib_staff','ksi')
                ->select('Name')
                ->where('Department','Sales')
                ->where('Employer','PPIB(HK)')
                ->where_any_is(array(
                    array('Work_Group' => 'New Business'),
                    array('Work_Group' => 'Expert')
                ))
                ->where('Expired_Date','0001-01-01')
                ->order_by_asc('Name')
                ->find_array();
        return $user;
    }
    
    /**
     * @param   string $username login username
     * @param   string $pass login password
     * @return   boolean true/false
     */
    public static function validateLogin($username,$pass) {
        $user = ORM::for_table('ppib_staff','ksi')->
                    where('User_Name', $username)->
                    where('PWD', $pass)->
                    find_one();
        if ( $user ) {
            //return $user;
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
}
