<?php
/**
 * Login / get salesList.
 */
namespace Ksi;

use ORM as ORM;

/**
 * Login / get salesList.
 */
class User
{
    /**
     * nothing.
     */
    public function __construct()
    {
    }

    /**
     * Find the sales list from KSI , order by asc.
     *
     * @return array array(userName1,uesrName2 ...)
     */
    public static function salesList()
    {
//        $user = ORM::for_table('ppib_staff', 'ksi')
//                ->select('Name')
//                ->where('Department', 'Sales')
//                ->where('Location', 'Singapore')
//                ->where_any_is([
//                    ['Work_Group' => 'New Business(SG)'],
//                    ['Work_Group' => 'Expert'],
//                ])
//                ->where('Expired_Date', '0001-01-01')
//                ->order_by_asc('Name')
//                ->find_array();
        $user = ORM::for_table('ppib_staff', 'ksi')
                ->select('Name')
                ->where('Region', 'SG')
                ->where('Expired_Date', '0001-01-01')
                ->order_by_asc('Name')
                ->find_array();

        return $user;
    }

    /**
     * validateLogin.
     *
     * @param string $username login username
     * @param string $pass     login password
     *
     * @return bool true/false
     */
    public static function validateLogin($username, $pass)
    {
        $user = ORM::for_table('ppib_staff', 'ksi')->
                    where('User_Name', $username)->
                    where('PWD', $pass)->
                    find_one();
        if ($user) {
            return true;
        }

        return false;
    }
}
