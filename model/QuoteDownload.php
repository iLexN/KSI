<?php

namespace Ksi;

use ORM as ORM;

/**
 * Quote Model.
 */
class QuoteDownload
{
    /**
     * @var object ORM-object
     */
    private $ormObjFromSource;

    /**
     * @var object ORM-Object
     */
    public $ormObjFromLocal;

    /**
     * nothing.
     */
    public function __construct()
    {
    }

    /**
     * Flow for process Download -> save , update , find dup.
     *
     * @param ORM $ormQuote orm-object
     */
    public function processDownload($ormQuote)
    {
        $this->ormObjFromSource = $ormQuote;
        //$this->saveDownload();
        $this->saveDownloadToKSI();
        $this->updateSourceDownload();
    }

    /**
     * Save the download data from source to local.

    private function saveDownload()
    {
        $this->ormObjFromLocal = ORM::for_table('motor_quote', 'local')->create();
        $this->ormObjFromLocal->set($this->ormObjFromSource->as_array());
        $this->ormObjFromLocal->save();
    }
     * */

    /**
     * Save the download data from source to local.
     */
    private function saveDownloadToKSI()
    {
        /* @var $yellowSheetOrm ORM */
        $yellowSheetOrm = ORM::for_table('ksi_sg_online', 'ksi')->create();
        $yellowSheetOrm->set($this->ormObjFromSource->as_array());
        $yellowSheetOrm->save();
    }

    /**
     * update the source which will no download again
     * this process after saveDownload().
     */
    private function updateSourceDownload()
    {
        $this->ormObjFromSource->download = 1;
        $this->ormObjFromSource->save();
    }
}
