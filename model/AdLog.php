<?php
/**
 * Login / get salesList.
 */
namespace Ksi;

use ORM as ORM;

/**
 * Login / get salesList.
 */
class AdLog
{
    /**
     * nothing.
     */
    public function __construct()
    {
    }

    /**
     * Find motor google adwords logs.
     *
     * @return array
     */
    public static function adLogList()
    {
        $adLog = ORM::for_table('sales_inte_online_inquiries', 'ksi')
                ->find_array();

        return $adLog;
    }
}
