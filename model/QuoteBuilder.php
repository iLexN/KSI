<?php

namespace Ksi;

use ORM as ORM;

/**
 * Description of QuoteBuilder.
 *
 * @author user
 */
class QuoteBuilder
{
    /**
     * Push 1 Quote to Sale.
     *
     * @param array $ar The Array of id,sale
     *
     * @return array email,rePush
     */
    public static function pushOneQuote($ar)
    {
        /* @var $oneQuoteOrm ORM */
        $oneQuoteOrm = ORM::for_table('ksi_sg_online', 'ksi')->
                find_one($ar['id']);

        if ($oneQuoteOrm->status === '0') {
            $q = new QuotePush($oneQuoteOrm);
            $q->proccessDataToYellowSheet($ar['sale']);

            return [
                    'email' => $q->ormObjFromLocal->Email,
                    'rePush' => 0,
                ];
        } else {
            return [
                    'email' => $oneQuoteOrm->Email,
                    'rePush' => 1,
                ];
        }
    }

    /**
     * list the quote have not push yet.
     *
     * @return array [ totalNumber , orm-object , listedNumber ]
     */
    public static function outstandingQuote()
    {
        $manyQuote = ORM::for_table('ksi_sg_online', 'ksi')->
                    where('status', 0);
        $total = $manyQuote->count();

        $manyQuote2 = $manyQuote->limit(50)->order_by_asc('Mobile')->order_by_asc('Email')->order_by_asc('id')->find_many();

        $quoteOrmAr = [];
        foreach ($manyQuote2 as $quoteOrm) {
            $quoteOrmAr[] = new \Ksi\QuoteLayout($quoteOrm);
        }

        $numberListed = count($quoteOrmAr);

        return [$total,
                    $quoteOrmAr,
                    $numberListed,
                ];
    }

    /**
     * Download the data from the source database, crontab use.
     *
     * @param string $t a = auto , m = not auto
     *
     * @return array downloaded Quote ID
     */
    public static function downloadQuote($t = 'a')
    {
        $manyQuoteOrm = ORM::for_table('motor_quote', 'source')->
                    where('download', 0)->
                    order_by_asc('id');

        if ($t == 'a') {
            $manyQuoteOrm->where_lt('create_datetime', date('Y-m-d H:i:s', strtotime('-1 minutes')));
        }

        $manyQuote = $manyQuoteOrm->find_many();

        $arQuoteIdAr = [];
        $q = new QuoteDownload();
        foreach ($manyQuote as $quoteOrm) {
            $q->processDownload($quoteOrm);
            $arQuoteIdAr[] = $quoteOrm->id;
        }

        return $arQuoteIdAr;
    }
}
