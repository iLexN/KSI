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
        $user = ORM::for_table('ppib_staff', 'ksi')
                ->select('Name')
                ->where('S_KSI', 1)
                ->where_not_equal('Work_Group', 'Agent')
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
