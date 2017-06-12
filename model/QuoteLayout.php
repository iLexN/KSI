<?php

namespace Ksi;

use ORM as ORM;

/**
 * Description of QuoteLayout.
 *
 * @author user
 */
class QuoteLayout implements \ArrayAccess
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
    }

    /**
     * layout use function.
     *
     * @return string <a>OldRef</a>
     */
    public function hasOldRefID()
    {
        if ($this->ormObjFromLocal->oldRefID != 0) {
            //$salename = $this->getHandlingSale();
            $salename = '';
            return '<a href="compare/'.$this->ormObjFromLocal->id.'" class="oldrefid">'.$this->ormObjFromLocal->oldRefID.'</a>' . $salename;
        }

        return '';
    }
/*
    private function getHandlingSale(){
        $ar = ORM::for_table('sales_intelligence', 'ksi')->where('Online_Ref_No',$this->ormObjFromLocal->oldRefID)
                ->find_one();

        if ( $ar ){
            return ' - '.$ar['Responsibility_Name'];
        }

        return '';
    }
 * */

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

    public function isCustomCarMark()
    {
        return (int)$this->ormObjFromLocal->carMake_key === 9999;
    }

    public function isCustomCarModel()
    {
        return is_numeric($this->ormObjFromLocal->carModel_key);
    }

    /**
     * map age range code.
     *
     * @return string
     */
    public function showAge()
    {
        switch ($this->ormObjFromLocal->age) {
            case 1:
                return '25-60';
            case 2:
                return '30–60';
            case 88:
                return '< 21';
            case 99:
                return '> 60';
            default:
                return $this->ormObjFromLocal->age;
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

    /**
     * sql copy from old script.
     *
     * set $this->ormObjFromLocal->ksi_si_no = string xx;xx;xx;
     */
    public function findKsiDuplicate()
    {
        $sqlValueAr = array(
            'tel' => $this->ormObjFromLocal->contactno,
            'email' => $this->ormObjFromLocal->email,
        );
        $sqlSearch = "
            SELECT `Sales_Intelligence_Number` , `Date_of_Contact` , `Responsibility_Name`
            FROM `sales_intelligence`
            WHERE
                ( REPLACE(Mobile, ' ', '')  = :tel ) OR
                ( REPLACE(Home_Phone, ' ', '')  = :tel ) OR
                ( REPLACE(Bus_Phone, ' ', '')  = :tel ) OR
                ( REPLACE(Email, ' ', '') = :email ) OR
                ( REPLACE(Client_Address_4, ' ', '') = :email )
            Order By `Sales_Intelligence_Number` desc
            Limit 1
        ";
        $ksiDuplicateAr = ORM::for_table('sales_intelligence', 'ksi')->
                raw_query($sqlSearch, $sqlValueAr)->
                find_array();
        if ($ksiDuplicateAr) {
            return $ksiDuplicateAr[0]['Date_of_Contact'].'<br/>'.$ksiDuplicateAr[0]['Responsibility_Name'];
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->ormObjFromLocal->$offset = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->ormObjFromLocal->$offset);
    }

    public function offsetUnset($offset)
    {
        unset($this->ormObjFromLocal->$offset);
    }

    public function offsetGet($offset)
    {
        return isset($this->ormObjFromLocal->$offset) ? $this->ormObjFromLocal->$offset : null;
    }
}
