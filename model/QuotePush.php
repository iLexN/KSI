<?php

namespace Ksi;

use ORM as ORM;

/**
 * push Quote.
 */
class QuotePush
{
    /**
     * @var ORM ORM-Object
     */
    public $ormObjFromLocal;

    public function __construct(ORM $orm)
    {
        $this->ormObjFromLocal = $orm;
    }

    /**
     * prog flow for pushing.
     *
     * @param string $sale saleName
     */
    public function proccessDataToYellowSheet($sale)
    {
        if ($sale !== 'Rubbish') {
            $this->dataToYellowSheet($sale);
            $this->updateLocalPushStatus(1);
            //$this->updatePushStatusToKSI(1);
        } else {
            $this->updateLocalPushStatus(2); // 2 for Rubbish
        }
    }

    /**
     * Save data from local to KSI, some data need process before save.
     *
     * @param string $sale saleName
     */
    private function dataToYellowSheet($sale)
    {
        $master = ORM::for_table('ksi_sg_master', 'ksi')->create();

        $master->Responsibility_Name = $sale;

        $master->Ownership = $this->ormObjFromLocal->Ownership;
        $master->Source = $this->ormObjFromLocal->Source;
        $master->Date_of_Contact = date('Y-m-d');
        $master->Policy_Status = $this->ormObjFromLocal->Policy_Status;
        $master->Email = $this->ormObjFromLocal->Email;
        $master->Mobile = $this->ormObjFromLocal->Mobile;
        $master->Policy_Details = $this->typeOfInsuranceKeyToText($this->ormObjFromLocal->Policy_Details);
        $master->Make = $this->ormObjFromLocal->Make;
        $master->Model = $this->ormObjFromLocal->Model;
        $master->Cylinder_Capacity = $this->ormObjFromLocal->Cylinder_Capacity;
        $master->Year_of_Manufacture = $this->ormObjFromLocal->Year_of_Manufacture;
        $master->Surname = $this->ormObjFromLocal->Surname;
        $master->first_name = $this->ormObjFromLocal->first_name;
        $master->title = $this->ormObjFromLocal->title;
        $master->Driver_One_Occupation = $this->ormObjFromLocal->Driver_One_Occupation;
        $master->Driver_One_Driving_Experience = $this->driverExpKeyToText($this->ormObjFromLocal->Driver_One_Driving_Experience);
        $master->Driver_Two_Occupation = $this->ormObjFromLocal->Driver_Two_Occupation;
        $master->Driver_Two_Driving_Experience = $this->driverExpKeyToText($this->ormObjFromLocal->Driver_Two_Driving_Experience);
        $master->client_language = 'en';
        $master->save();

        $insertID = $master->id();

        $motor = ORM::for_table('ksi_sg_motor', 'ksi')->create();
        $motor->ksi_no = $insertID;
        $motor->m1_effective_date = $this->ormObjFromLocal->m1_effective_date;
        $motor->m1_year_reg = $this->ormObjFromLocal->m1_year_reg;
        $motor->m1_off_peak = $this->ormObjFromLocal->m1_off_peak;
        $motor->m1_p_id_type = $this->ormObjFromLocal->m1_p_id_type;
        $motor->m1_p_id_no = $this->ormObjFromLocal->m1_p_id_no;
        $motor->m1_p_marital = $this->ormObjFromLocal->m1_p_marital;
        $motor->m1_p_dob = $this->emptyNullValue($this->ormObjFromLocal->m1_p_dob);
        $motor->m1_p_motoring_offences = $this->ormObjFromLocal->m1_p_motoring_offences;
        $motor->m1_p_demerit_points = $this->ormObjFromLocal->m1_p_demerit_points;
        $motor->m1_p_ncd = $this->ormObjFromLocal->m1_p_ncd;
        $motor->m1_p_offence_free = $this->ormObjFromLocal->m1_p_offence_free;
        $motor->m1_s_name = $this->ormObjFromLocal->m1_s_name;
        $motor->m1_s_id_type = $this->ormObjFromLocal->m1_s_id_type;
        $motor->m1_s_id_no = $this->ormObjFromLocal->m1_s_id_no;
        $motor->m1_s_title = $this->ormObjFromLocal->m1_s_title;
        $motor->m1_s_marital = $this->ormObjFromLocal->m1_s_marital;
        $motor->m1_s_dob = $this->emptyNullValue($this->ormObjFromLocal->m1_s_dob) ;
        $motor->m1_s_motoring_offences = $this->ormObjFromLocal->m1_s_motoring_offences;
        $motor->m1_s_demerit_points = $this->ormObjFromLocal->m1_s_demerit_points;
        $motor->save();

        $quote = ORM::for_table('ksi_sg_quote', 'ksi')->create();
        $quote->ksi_no = $insertID;
        $quote->save();

    }

    /**
     * add ad data to ad table.
     */
    private function adDataToYellowSheet()
    {
        $yellowSheetOrm = ORM::for_table('sales_inte_online_inquiries', 'ksi')->create();
        $yellowSheetOrm->Online_Ref_No = $this->ormObjFromLocal->id;
        $yellowSheetOrm->keywords = $this->ormObjFromLocal->keywords;
        $yellowSheetOrm->cmid = $this->ormObjFromLocal->cmid;
        $yellowSheetOrm->dgid = $this->ormObjFromLocal->dgid;
        $yellowSheetOrm->kwid = $this->ormObjFromLocal->kwid;
        $yellowSheetOrm->netw = $this->ormObjFromLocal->netw;
        $yellowSheetOrm->dvce = $this->ormObjFromLocal->dvce;
        $yellowSheetOrm->crtv = $this->ormObjFromLocal->crtv;
        $yellowSheetOrm->adps = $this->ormObjFromLocal->adps;
        $yellowSheetOrm->save();
    }

    /**
     * prog flow for pushing.
     *
     * @param int $s 0 default for watting action, 1 for pushed, 2 for Rubbish
     */
    private function updateLocalPushStatus($s)
    {
        $this->ormObjFromLocal->status = $s;
        $this->ormObjFromLocal->save();
    }

    private function typeOfInsuranceKeyToText($key)
    {
        switch ($key) {
            case 'Comprehensive':
                return 'Comprehensive';
            case 'ThirdPartyFireAndTheft':
                return 'Third Party + Fire and Theft';
            case 'ThirdParty':
                return 'Third Party';
            default:

        }
    }

    private function driverExpKeyToText($k)
    {
        switch ($k) {
            case 'lt1':
                return '"< 1 Year';
            case '15-20':
                return '10 - 15 Years';
            case 'gt20':
                return '+20 Years';
            case '1':
                return '1 Year';
            default:
                return $this->ormObjFromLocal->Driver_One_Driving_Experience.' Years';
        }
    }

    /**
     * set null value when empty.
     *
     * @param string $v
     */
    private function emptyNullValue($v)
    {
        if (empty($v)) {
            return NULL;
        }
        return $v;
    }
}
