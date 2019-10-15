<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 7:28 AM
 */

namespace SXSuite;
require "Modules/Field.php";
require "Modules/Lookup.php";

require "Modules/Checkbox.php";
require "Modules/CheckboxGroup_DB.php";
require "Modules/Date.php";
require "Modules/Dropdown_DB.php";
require "Modules/Dropdown_FS.php";
require "Modules/File.php";
require "Modules/File_Image_DB.php";
require "Modules/LiveQuery.php";
require "Modules/Number.php";
require "Modules/Radio_DB.php";
require "Modules/Signature.php";
require "Modules/Template.php";
require "Modules/Text.php";
require "Modules/TextArea.php";

class SXSuite
{
    protected $record;
    protected $viewMode;
    protected $useNA;

    function __construct($record = [])
    {
        if ($record)
            $this->record = $record;

        $this->viewMode = false;
        $this->useNA = false;
    }

    public function viewMode(bool $viewMode = null)
    {
        /**
         * set or get whether this class instance is in view mode
         * if passed without an argument, it will return the current state of the view mode
         * if an argument is passed, it will set the view mode, then return the value
         */
        if ($viewMode !== null)
            $this->viewMode = $viewMode === true;

        return $this->viewMode;
    }

    public function useNAforBlankValues(bool $useNA = null)
    {
        /**
         * set or get whether this class instance should use the text "NA" for rendered field values if they are blank
         * if passed without an argument, it will return the state of $this->useNAForBlankValues
         * if an argument is passed, it will set $this->useNAForBlankValues based on the passed value
         */
        if ($useNA !== null)
            $this->useNA = $useNA === true;

        return $this->useNA;
    }

}
