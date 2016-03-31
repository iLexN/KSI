<?php
/**
 *
 * Quote Model
 * starting static function listed
 * downloadQuote()
 * outstandingQuote()
 * pushOneQuote().
 */

namespace Ksi;

use ORM as ORM;

/**
 * starting static function listed
 * downloadQuote()
 * outstandingQuote()
 * pushOneQuote().
 */
class Quote
{
    /**
     * @var Object ORM-object
     */
    private $ormObjFromSource;

    /**
     * @var Object ORM-Object
     */
    public $ormObjFromLocal;

    /**
     * nothing
     */
    public function __construct()
    {
    }

    /**
     * Flow for process Download -> save , update , find dup.
     *
     * @param object $ormQuote orm-object
     */
    public function processDownload($ormQuote)
    {
        $this->ormObjFromSource = $ormQuote;
        $this->saveDownload();
        $this->saveDownloadToKSI();
        $this->updateSourceDownload();
        //$this->findDuplicate();
    }

    /**
     * set the ORM object for Locat DB
     *
     * @param object $ormObj orm-object
     */
    public function setOrmObjFromLocal($ormObj)
    {
        $this->ormObjFromLocal = $ormObj;
    }

    /**
     * Save the download data from source to local
     */
    private function saveDownload()
    {
        $this->ormObjFromLocal = ORM::for_table('motor_quote', 'local')->create();
        $this->ormObjFromLocal->set($this->ormObjFromSource->as_array());
        $this->ormObjFromLocal->save();
    }

    /**
     * Save the download data from source to local
     */
    private function saveDownloadToKSI()
    {
        /* @var $yellowSheetOrm ORM */
        $yellowSheetOrm = ORM::for_table('sales_inte_online', 'ksi')->create();
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

        $this->ormObjFromSource->hkid_1 = '';
        $this->ormObjFromSource->hkid_2 = '';
        $this->ormObjFromSource->hkid_3 = '';

        $this->ormObjFromSource->hkid_1_2 = '';
        $this->ormObjFromSource->hkid_2_2 = '';
        $this->ormObjFromSource->hkid_3_2 = '';

        $this->ormObjFromSource->dob = '';
        $this->ormObjFromSource->dob2 = '';

        $this->ormObjFromSource->save();
    }

    /**
     * flow:
     * findKsiDuplicate()
     * findYellowSheetDuplicate()
     * $this->ormObjFromLocal->save();.
     */
    private function findDuplicate()
    {
        $this->findKsiDuplicate();
        $this->findYellowSheetDuplicate();
        $this->ormObjFromLocal->save();
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
        return ;
    }

    /**
     * layout use function.
     *
     * @return string <a>OldRef</a>
     */
    public function hasOldRefID()
    {
        if ($this->ormObjFromLocal->oldRefID != 0) {
            return '<a href="compare/'.$this->ormObjFromLocal->id.'" class="oldrefid">'.$this->ormObjFromLocal->oldRefID . '</a>';
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
            case 'hk':
            case 'ch':
            case 'chi':
            case 'zh':
                return 'Chi';
                break;
            default:
                return 'Eng';
                break;
        }
    }

    public function showAge(){
        switch ($this->ormObjFromLocal->age) {
            case 1:
                return '25-60';
            case 88:
                return '< 21';
            case 99:
                return '> 60';
            default :
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

        $outArray = array();

        foreach ($jsonArray as $planArray) {
            $str = '';
            if (array_key_exists('planName', $planArray)) {
                $str .=  $planArray['planName'];
            }
            if (array_key_exists('subPlanName', $planArray)) {
                if (is_array($planArray['subPlanName'])) {
                    $str .= " - ".implode(' & ', $planArray['subPlanName']);
                } else {
                    $str .= " - ".$planArray['subPlanName'];
                }
            }
            $outArray[] = $str;
        }

        return implode(',', $outArray);
    }
    // layout use function end

    /**
     * sql copy from old script.
     */
    private function findYellowSheetDuplicate()
    {
        $sqlValueAr = array(
            'tel' => $this->ormObjFromLocal->contactno,
            'email' => $this->ormObjFromLocal->email,
        );
        $sqlSearch = "
		SELECT `Client_NO`
		FROM `client`
		WHERE
		( TRIM(IFNULL(`Home_Phone`,'')) <> '' AND REPLACE(`Home_Phone`, ' ', '') = :tel ) OR
		( TRIM(IFNULL(`Mobile_One`,'')) <> '' AND REPLACE(`Mobile_One`, ' ', '') = :tel ) OR
		( TRIM(IFNULL(`Mobile_Two`,'')) <> '' AND REPLACE(`Mobile_Two`, ' ', '') = :tel ) OR
		( TRIM(IFNULL(`Business_Phone`,'')) <> '' AND REPLACE(`Business_Phone`, ' ', '') = :tel ) OR
		( TRIM(IFNULL(`Person_One_Mobile`,'')) <> '' AND REPLACE(`Person_One_Mobile`, ' ', '') = :tel ) OR
		( TRIM(IFNULL(`Person_One_Home`,'')) <> '' AND REPLACE(`Person_One_Home`, ' ', '') = :tel ) OR
		( TRIM(IFNULL(`Mailer_Phone`,'')) <> '' AND REPLACE(`Mailer_Phone`, ' ', '') = :tel ) OR
		( TRIM(IFNULL(`Contact_Phone`,'')) <> '' AND REPLACE(`Contact_Phone`, ' ', '') = :tel )
		";
        $ysDuplicateAr = ORM::for_table('sales_intelligence', 'ksi')->
                raw_query($sqlSearch, $sqlValueAr)->
                find_array();
        if ($ysDuplicateAr) {
            $this->ormObjFromLocal->ys_client_no = implode(';', array_column($ysDuplicateAr, 'Client_NO'));
        }
    }

    /**
     * sql copy from old script.
     *
     * set $this->ormObjFromLocal->ksi_si_no = string xx;xx;xx;
     */
    private function findKsiDuplicate()
    {
        $sqlValueAr = array(
            'tel' => $this->ormObjFromLocal->contactno,
            'email' => $this->ormObjFromLocal->email,
        );

        $sqlSearch = "
            SELECT `Sales_Intelligence_Number`
            FROM `sales_intelligence`
            WHERE
                ( TRIM(IFNULL(`mobile`,'')) <> '' AND REPLACE(`mobile`, ' ', '') = :tel ) OR
                ( TRIM(IFNULL(`home_phone`,'')) <> '' AND REPLACE(`home_phone`, ' ', '') = :tel ) OR
                ( TRIM(IFNULL(`bus_phone`,'')) <> '' AND REPLACE(`bus_phone`, ' ', '') = :tel ) OR
                ( TRIM(IFNULL(`email`,'')) <> '' AND REPLACE(`email`, ' ', '') = :email )
        ";
        $ksiDuplicateAr = ORM::for_table('sales_intelligence', 'ksi')->
                raw_query($sqlSearch, $sqlValueAr)->
                find_array();
        if ($ksiDuplicateAr) {
            $this->ormObjFromLocal->ksi_si_no = implode(';', array_column($ksiDuplicateAr, 'Sales_Intelligence_Number'));
        }
    }

    /**
     * Download the data from the source database, crontab use.
     *
     * @param string $t a = auto , m = not auto
     * @return array downloaded Quote ID
     */
    public static function downloadQuote($t = 'a')
    {
        $manyQuoteOrm = ORM::for_table('motor_quote', 'source')->
                    where('download', 0)->
                    order_by_asc('id');

        if ($t == 'a') {
            $manyQuoteOrm->where_lt('create_datetime', date("Y-m-d H:i:s", strtotime("-15 minutes")));
        }

        $manyQuote = $manyQuoteOrm->find_many();

        $arQuoteIdAr = array();
        foreach ($manyQuote as $quoteOrm) {
            $q = new Quote();
            $q->processDownload($quoteOrm);
            $arQuoteIdAr[] = $quoteOrm->id;
        }

        return $arQuoteIdAr;
    }

    /**
     * list the quote have not push yet.
     *
     * @return array [ totalNumber , orm-object , listedNumber ]
     */
    public static function outstandingQuote()
    {
        $manyQuote = ORM::for_table('sales_inte_online', 'ksi')->
                    where('status', 0);
        $total = $manyQuote->count();

        $manyQuote2 = $manyQuote->limit(50)->order_by_asc('contactno')->order_by_asc('email')->order_by_asc('id')->find_many();

        $quoteOrmAr = array();
        foreach ($manyQuote2 as $quoteOrm) {
            $q = new Quote();
            $q->setOrmObjFromLocal($quoteOrm);
            $quoteOrmAr[] = $q;
        }

        $numberListed = count($quoteOrmAr);

        return array($total,
                    $quoteOrmAr,
                    $numberListed,
                );
    }

    /**
     * Push 1 Quote to Sale.
     *
     * @param array $ar The Array of id,sale
     *
     * @return array email,rePush
     */
    public static function pushOneQuote($ar)
    {
        $oneQuoteOrm = ORM::for_table('sales_inte_online', 'ksi')->
                find_one($ar['id']);

        if ($oneQuoteOrm->status === '0') {
            $q = new Quote();
            $q->setOrmObjFromLocal($oneQuoteOrm);
            $q->proccessDataToYellowSheet($ar['sale']);

            return array(
                    'email' => $q->ormObjFromLocal->email,
                    'rePush' => 0,
                );
        } else {
            return array(
                    'email' => $oneQuoteOrm->email,
                    'rePush' => 1,
                );
        }
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
            $this->haveAdData(); // if have ad data than insert to ad table
            $this->updateLocalPushStatus(1);
            //$this->updatePushStatusToKSI(1);
        } else {
            $this->updateLocalPushStatus(2); // 2 for Rubbish
            //$this->updatePushStatusToKSI(2);
        }
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

    private function updatePushStatusToKSI($s)
    {
        $ksiOrm = ORM::for_table('sales_inte_online', 'ksi')->
                where('id',  $this->ormObjFromLocal->id)->
                find_one();
        $ksiOrm->status = $s;
        $ksiOrm->save();
    }

    /**
     * Save data from local to KSI, some data need process before save.
     *
     * @param string $sale saleName
     */
    private function dataToYellowSheet($sale)
    {
        $yellowSheetOrm = ORM::for_table('sales_intelligence', 'ksi')->create();

        $yellowSheetOrm->Responsibility_Name = $sale;
        $yellowSheetOrm->Date_of_Contact = date('Y-m-d');
        $yellowSheetOrm->Ownership = $this->ormObjFromLocal->referer;
        $yellowSheetOrm->client_language  = $this->ormObjFromLocal->lang ;
        $yellowSheetOrm->First_Name = mb_convert_encoding($this->ormObjFromLocal->name, "BIG5", "UTF-8");
        $yellowSheetOrm->Mobile = $this->ormObjFromLocal->contactno;
        $yellowSheetOrm->Email = $this->ormObjFromLocal->email;
        $yellowSheetOrm->Client_Address_4 = $this->emptyNullValue($this->ormObjFromLocal->email2);
        $yellowSheetOrm->Client_Address_1 = $this->emptyNullValue(mb_convert_encoding($this->ormObjFromLocal->address, "BIG5", "UTF-8"));
        $yellowSheetOrm->Client_Address_2 = $this->emptyNullValue(mb_convert_encoding($this->ormObjFromLocal->address2, "BIG5", "UTF-8"));
        $yellowSheetOrm->Client_Address_3 = $this->emptyNullValue(mb_convert_encoding($this->ormObjFromLocal->address3, "BIG5", "UTF-8"));
        $yellowSheetOrm->Client_Address_5 = $this->emptyNullValue(mb_convert_encoding($this->ormObjFromLocal->address4, "BIG5", "UTF-8"));
        $yellowSheetOrm->Policy_Details = $this->ormObjFromLocal->insuranceType;

        $yellowSheetOrm->Sum_Insured = $this->ormObjFromLocal->sum_insured;

        $yellowSheetOrm->Make = $this->ormObjFromLocal->carMake;
        $yellowSheetOrm->Model =  mb_convert_encoding($this->ormObjFromLocal->carModel, "BIG5", "UTF-8");
        $yellowSheetOrm->Year_of_Manufacture = !empty($this->ormObjFromLocal->yearManufacture) ? $this->ormObjFromLocal->yearManufacture : 2000; // default value of the database;
        $yellowSheetOrm->Driver_One_Age = $this->ormObjFromLocal->age;
        $yellowSheetOrm->Driver_One_Driving_Experience = html_entity_decode($this->ormObjFromLocal->drivingExp);
        $yellowSheetOrm->Driver_One_Occupation = mb_convert_encoding(html_entity_decode($this->ormObjFromLocal->occupation), "BIG5", "UTF-8");

        $yellowSheetOrm->NCD = $this->ormObjFromLocal->ncd;

        $yellowSheetOrm->ksi_si_no = $this->ormObjFromLocal->ksi_si_no;
        $yellowSheetOrm->ys_client_no = $this->ormObjFromLocal->ys_client_no;

        // plan info break down
        $planArray = $this->jsonDcodePlans();
        //print_r($planArray);

        $yellowSheetOrm->auto_premium  = !empty($planArray) ? 1 : 0 ;

        if (isset($planArray[0])) {
            $yellowSheetOrm->Quote_One_NCD = $this->ormObjFromLocal->ncd;
            $yellowSheetOrm->Quote_One_Insurance_Type = $planArray[0]['TypeofInsurance'];
            $yellowSheetOrm->Quote_One_Estimated_Value = $planArray[0]['sum_insured'];
            $yellowSheetOrm->Quote_One_NetP_w_Tax = $planArray[0]['price'];
            $yellowSheetOrm->Quote_One_Special_Offer_Premium = $planArray[0]['totalPrice'];
            $yellowSheetOrm->Quote_One_TPPD_Excess = $planArray[0]['details'][1]['value'];
            $yellowSheetOrm->Quote_One_TPPD_Unnamed_Excess = $planArray[0]['details']['2']['value'];
            $yellowSheetOrm->Quote_One_TPPD_Young_Excess = $planArray[0]['details']['3']['value'];
            $yellowSheetOrm->Quote_One_TPPD_Inexperienced_Excess = $planArray[0]['details']['4']['value'];
            $yellowSheetOrm->Quote_One_General_Excess = '$' . $planArray[0]['details']['5']['value'];
            $yellowSheetOrm->Quote_One_Unnamed_Excess = $planArray[0]['details']['6']['value'];
            $yellowSheetOrm->Quote_One_Young_Excess = $planArray[0]['details']['7']['value'];
            $yellowSheetOrm->Quote_One_Inexperienced_Excess = $planArray[0]['details']['8']['value'];
            $yellowSheetOrm->Quote_One_Parking_Excess = $planArray[0]['details']['9']['value'];
            $yellowSheetOrm->Quote_One_Theft_Excess = '$' . $planArray[0]['details']['10']['value'];
            $yellowSheetOrm->Quote_One_Premium = $planArray[0]['premium'];
            $yellowSheetOrm->Quote_One_Loading = $planArray[0]['loading'];
            $yellowSheetOrm->Quote_One_Other_Discount = $planArray[0]['otherDiscount'];
            $yellowSheetOrm->Quote_One_Client_Discount = $planArray[0]['clientDiscount'];
            $yellowSheetOrm->Quote_One_Commission = $planArray[0]['commission'];
            $yellowSheetOrm->Quote_One_MIB = $planArray[0]['mib'];
            $yellowSheetOrm->Quote_One_Gross_Premium = $planArray[0]['gross'];
            $yellowSheetOrm->Additional_Plan = implode(',', $planArray[0]['subPlanName']);
        }

        if (isset($planArray[1])) {
            $yellowSheetOrm->Quote_Two_NCD = $this->ormObjFromLocal->ncd;
            $yellowSheetOrm->Quote_Two_Insurance_Type = $planArray[1]['TypeofInsurance'];
            $yellowSheetOrm->Quote_Two_Estimated_Value = $planArray[1]['sum_insured'];
            $yellowSheetOrm->Quote_Two_NetP_w_Tax = $planArray[1]['price'];
            $yellowSheetOrm->Quote_Two_Special_Offer_Premium = $planArray[1]['totalPrice'];
            $yellowSheetOrm->Quote_Two_TPPD_Excess = $planArray[1]['details'][1]['value'];
            $yellowSheetOrm->Quote_Two_TPPD_Unnamed_Excess = $planArray[1]['details']['2']['value'];
            $yellowSheetOrm->Quote_Two_TPPD_Young_Excess = $planArray[1]['details']['3']['value'];
            $yellowSheetOrm->Quote_Two_TPPD_Inexperienced_Excess = $planArray[1]['details']['4']['value'];
            $yellowSheetOrm->Quote_Two_General_Excess = '$' . $planArray[1]['details']['5']['value'];
            $yellowSheetOrm->Quote_Two_Unnamed_Excess = $planArray[1]['details']['6']['value'];
            $yellowSheetOrm->Quote_Two_Young_Excess = $planArray[1]['details']['7']['value'];
            $yellowSheetOrm->Quote_Two_Inexperienced_Excess = $planArray[1]['details']['8']['value'];
            $yellowSheetOrm->Quote_Two_Parking_Excess = $planArray[1]['details']['9']['value'];
            $yellowSheetOrm->Quote_Two_Theft_Excess = '$' . $planArray[1]['details']['10']['value'];
            $yellowSheetOrm->Quote_Two_Premium = $planArray[1]['premium'];
            $yellowSheetOrm->Quote_Two_Loading = $planArray[1]['loading'];
            $yellowSheetOrm->Quote_Two_Other_Discount = $planArray[1]['otherDiscount'];
            $yellowSheetOrm->Quote_Two_Client_Discount = $planArray[1]['clientDiscount'];
            $yellowSheetOrm->Quote_Two_Commission = $planArray[1]['commission'];
            $yellowSheetOrm->Quote_Two_MIB = $planArray[1]['mib'];
            $yellowSheetOrm->Quote_Two_Gross_Premium = $planArray[1]['gross'];
        }


        $yellowSheetOrm->Start_Date = $this->dateFormate($this->ormObjFromLocal->policy_start_date);
        $yellowSheetOrm->Policy_End_Date = $this->dateFormate($this->ormObjFromLocal->policy_end_date);

        //new extra fields
        $yellowSheetOrm->Online_Ref_No = $this->ormObjFromLocal->id;
        $yellowSheetOrm->Gender = (!empty($this->ormObjFromLocal->gender)) ? strtoupper($this->ormObjFromLocal->gender[0]) : '';
        $yellowSheetOrm->Client_Address_6 = $this->emptyNullValue(mb_convert_encoding($this->ormObjFromLocal->residential_district, "BIG5", "UTF-8"));//address line 5
        $yellowSheetOrm->Marital_Status = $this->ormObjFromLocal->marital_status;
        $yellowSheetOrm->DOB = $this->dateFormate($this->ormObjFromLocal->dob);

        //$yellowSheetOrm->Vehicle_Registration = $this->ormObjFromLocal->vehicle_registration;
        $yellowSheetOrm->Vehicle_Registration = mb_convert_encoding($this->ormObjFromLocal->vehicle_registration, "BIG5", "UTF-8");

        $yellowSheetOrm->Yearly_Mileage = $this->ormObjFromLocal->yearly_mileage;
        $yellowSheetOrm->Motor_Accident_Yrs = $this->ormObjFromLocal->motor_accident_yrs;
        $yellowSheetOrm->Drive_Offence_Point = $this->ormObjFromLocal->drive_offence_point;
        $yellowSheetOrm->Drive_To_Work = $this->ormObjFromLocal->drive_to_work;
        $yellowSheetOrm->Course_Of_Work = $this->ormObjFromLocal->course_of_work;
        $yellowSheetOrm->Convictions_5_Yrs = $this->ormObjFromLocal->convictions_5_yrs;
        $yellowSheetOrm->HKID = $this->hkidFormate($this->ormObjFromLocal->hkid_1, $this->ormObjFromLocal->hkid_2, $this->ormObjFromLocal->hkid_3);
        $yellowSheetOrm->Pay_Button_Click = $this->ormObjFromLocal->payButtonClick;
        $yellowSheetOrm->Online_Submit_Datetime = $this->ormObjFromLocal->create_datetime;

        //driver2
        $yellowSheetOrm->Driver_Two_Name = mb_convert_encoding($this->ormObjFromLocal->name2, "BIG5", "UTF-8");
        $yellowSheetOrm->Driver_Two_DOB = $this->dateFormate($this->ormObjFromLocal->dob2);
        $yellowSheetOrm->Driver_Two_Age = $this->emptyNullValue($this->ormObjFromLocal->age2);
        $yellowSheetOrm->Driver_Two_Driving_Experience = $this->emptyNullValue(html_entity_decode($this->ormObjFromLocal->drivingExp2));
        $yellowSheetOrm->Driver_Two_Occupation = $this->emptyNullValue(mb_convert_encoding(html_entity_decode($this->ormObjFromLocal->occupation2), "BIG5", "UTF-8"));
        $yellowSheetOrm->Relationship = $this->emptyNullValue($this->ormObjFromLocal->relationship2);
        $yellowSheetOrm->Driver_Two_Gender = (!empty($this->ormObjFromLocal->gender2)) ? strtoupper($this->ormObjFromLocal->gender2[0]) : '';
        $yellowSheetOrm->Driver_Two_HKID = $this->hkidFormate($this->ormObjFromLocal->hkid_1_2, $this->ormObjFromLocal->hkid_2_2, $this->ormObjFromLocal->hkid_3_2);
        $yellowSheetOrm->Driver_Two_Marital_Status = $this->ormObjFromLocal->marital_status2;
        //$yellowSheetOrm->Driver_Two_Residential_District = $this->ormObjFromLocal->residential_district2;
        $yellowSheetOrm->Driver_Two_Drive_Offence_Point = $this->ormObjFromLocal->drive_offence_point2;
        $yellowSheetOrm->Driver_Two_Motor_Accident_Yrs = $this->ormObjFromLocal->motor_accident_yrs2;

        //car additational info
        $yellowSheetOrm->Body_Type = $this->emptyNullValue($this->ormObjFromLocal->bodyType);
        $yellowSheetOrm->Number_of_Doors = $this->ormObjFromLocal->numberOfDoors;
        $yellowSheetOrm->Chassis_Number = $this->ormObjFromLocal->chassisNumber;
        $yellowSheetOrm->Engine_Number = $this->ormObjFromLocal->engineNumber;
        $yellowSheetOrm->Cylinder_Capacity = $this->ormObjFromLocal->cylinderCapacity;
        $yellowSheetOrm->Number_of_Seats = $this->ormObjFromLocal->numberOfSeats;

        $yellowSheetOrm->save();
    }

    private function haveAdData()
    {
        if (
                !empty($this->ormObjFromLocal->cmid) ||
                !empty($this->ormObjFromLocal->dgid) ||
                !empty($this->ormObjFromLocal->kwid) ||
                !empty($this->ormObjFromLocal->netw) ||
                !empty($this->ormObjFromLocal->dvce) ||
                !empty($this->ormObjFromLocal->crtv) ||
                !empty($this->ormObjFromLocal->adps)
            ) {
            $this->adDataToYellowSheet();
        }
        return;
    }

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

        //echo($this->ormObjFromLocal->adps);

        $yellowSheetOrm->save();
    }

    /**
     * data formate process to yellow sheet for HKID.
     *
     * @param string $a HKID part1
     * @param string $b HKID part2
     * @param string $c HKID part3
     *
     * @return string $a.$b.($c)
     */
    private function hkidFormate($a, $b, $c)
    {
        if (!empty($a) && !empty($b) && !empty($c)) {
            return $a.$b.'('.$c.')';
        } else {
            return '';
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
            return;
        }

        return $v;
    }

    /**
     * data formate process to yellow sheet for date.
     *
     * @param string $d dd-mm-yyyy
     *
     * @return string yyyy-mm-dd or Null
     */
    private function dateFormate($d)
    {
        if (!empty($d) && $d != '00-00-0000') {
            $dateArray = explode('-', $d);

            return $dateArray[2].'-'.$dateArray[1].'-'.$dateArray[0];
        } else {
            return;
        }
    }

    /**
     * data formate process to yellow sheet for Plans Info.
     *
     * @return array with default value
     */
    private function jsonDcodePlans()
    {
        $jsonArray = json_decode($this->ormObjFromLocal->plan_match_json, true);
        $outArray = array();
        foreach ($jsonArray as $rowKey => $planArray) {
            if (isset($planArray['details'])) {
                $newArray = array();
                foreach ($planArray['details'] as $vAr) {
                    $newArray[$vAr['deatils_id']] = $vAr;
                }
                $planArray['details'] = $newArray;
            }
            $planArray['TypeofInsurance'] = $this->typeOfInsuranceKeyToText($planArray['TypeofInsurance']);
            $planArray['sum_insured'] = ($planArray['TypeofInsurance'] == 'Comprehensive') ? $this->ormObjFromLocal->sum_insured : '0.00';
            // set default value array
            $returnArray = array(
                'planName' => '',
                'TypeofInsurance' => '',
                'totalPrice' => '0.00',
                'price' => '0.00',
                'sum_insured' => '0.00',
                'details' => array('1' => array('value' => '0.00'),//TPPD
                                '2' => array('value' => '0.00'),//TPPD U
                                '3' => array('value' => '0.00'),//TPPD Y
                                '4' => array('value' => '0.00'),//TPPD i
                                '5' => array('value' => '0.00'),//General
                                '6' => array('value' => '0.00'),//Unnamed
                                '7' => array('value' => '0.00'),//Young
                                '8' => array('value' => '0.00'),//Inexp
                                '9' => array('value' => '0.00'),//Parking
                                '10' => array('value' => '0.00'),//Theft
                            ),
                'subPlanName' => array(),
                'premium' => '0.00',
                'loading' => '0.00',
                'otherDiscount' => '0.00',
                'clientDiscount' => '0.00',
                'commission' => '0.00',
                'mib' => '0.00',
                'gross' => '0.00',
            );

            $outArray[] = array_replace_recursive($returnArray, $planArray);
        }
        return $outArray;
    }
    // data formate process to yellow sheet end


    private function typeOfInsuranceKeyToText($key)
    {
        $outArray['Third_Party_Only'] = 'Third Party';
        $outArray['Comprehensive'] = 'Comprehensive';
        $outArray['Comprehensive_Third_Party'] = 'Third Party and Comp.';
        return $outArray[$key];
    }
}
