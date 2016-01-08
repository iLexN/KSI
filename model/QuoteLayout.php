<?php

namespace Ksi;

use ORM as ORM;

/**
 * Description of QuoteLayout.
 *
 * @author user
 */
class QuoteLayout
{
    /* @var $ormObjFromLocal ORM */
    public $ormObjFromLocal;

    /**
     * @param ORM $orm
     */
    public function __construct(ORM $orm)
    {
        $this->ormObjFromLocal = $orm;
    }

    /**
     * layout use function.
     *
     * @return string Yes/No
     */
    public function isDuplicate()
    {
        if ($this->ormObjFromLocal->ksi_si_no !== '' ||
             $this->ormObjFromLocal->ys_client_no !== '') {
            return 'Yes';
        }

        return 'No';
    }

    /**
     * layout use function.
     *
     * @return string Yes/No
     */
    public function isPayButtonClick()
    {
        if ($this->ormObjFromLocal->payButtonClick == 1) {
            return 'Yes';
        }

        return;
    }

    /**
     * layout use function.
     *
     * @return string <a>OldRef</a>
     */
    public function hasOldRefID()
    {
        if ($this->ormObjFromLocal->oldRefID != 0) {
            return '<a href="compare/'.$this->ormObjFromLocal->id.'" class="oldrefid">'.$this->ormObjFromLocal->oldRefID.'</a>';
        }

        return '';
    }

    /**
     * layout use function.
     *
     * @return string Chi/Eng
     */
    public function langKeyMap()
    {
        switch ($this->ormObjFromLocal->lang) {
            case 'zh':
                return 'Chi';
            default:
                return 'Eng';
        }
    }

    /**
     * layout use function.
     *
     * @return string planName - SubPlaneName
     */
    public function isPlans()
    {
        $jsonArray = json_decode($this->ormObjFromLocal->plan_match_json, true);

        $outArray = [];

        foreach ($jsonArray as $planArray) {
            $str = '';
            if (array_key_exists('planName', $planArray)) {
                $str .=  $planArray['planName'];
            }
            if (array_key_exists('subPlanName', $planArray)) {
                if (is_array($planArray['subPlanName'])) {
                    $str .= ' - '.implode(' & ', $planArray['subPlanName']);
                } else {
                    $str .= ' - '.$planArray['subPlanName'];
                }
            }
            $outArray[] = $str;
        }

        return implode(',', $outArray);
    }
}
