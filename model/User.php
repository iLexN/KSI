<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Ksi;

use ORM as ORM;

/**
 * Description of User
 *
 * @author user
 */
class User {
    //put your code here
    //private $userTbl = 'ppib_staff';


    public function __construct() {
        
    }
    
    public static function salesList(){
        /*$sql_saleslist = "select `Name` from $saleslist 
		WHERE `Department` = 'Sales' 
		AND `Employer` IN ( 'PPIB(HK)' )
		AND `Work_Group` IN ( 'New Business' , 'Expert' )
		AND `Expired_Date` = '0001-01-01' 
		ORDER BY Name ASC";
         */
        $user = ORM::for_table('ppib_staff','ksi')
                ->select('Name')
                ->where('Department','Sales')
                //->where('Employer','PPIB(HK)')
                ->where('Location','Hong Kong')
                ->where_any_is(array(
                    array('Work_Group' => 'New Business'),
                    array('Work_Group' => 'Expert')
                ))
                ->where('Expired_Date','0001-01-01')
                ->order_by_asc('Name')
                ->find_array();
        return $user;
    }
    
    
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
