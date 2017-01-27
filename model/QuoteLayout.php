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
     * @return string Chi/Eng
     */
    public function insTypeMap()
    {
        switch ($this->ormObjFromLocal->Policy_Details) {
            case 'Comprehensive':
                return 'Comprehensive';
            case 'ThirdPartyFireAndTheft':
                return 'Third Party + Fire and Theft';
            case 'ThirdParty':
                return 'Third Party';
            default:

        }
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
     * map age range code.
     *
     * @return string
     */
    public function showDriverExp()
    {
        switch ($this->ormObjFromLocal->Driver_One_Driving_Experience) {
            case 'lt1':
                return '< 1 Year';
            case '15-20':
                return '15 - 20 Years';
            case 'gt20':
                return '+20 Years';
            case '1':
                return '1 Year';
            default:
                return $this->ormObjFromLocal->Driver_One_Driving_Experience.' Years';
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
