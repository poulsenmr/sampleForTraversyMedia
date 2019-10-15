<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:16 AM
 */

namespace SXSuite;
class File_Image_DB extends File
{
    private $___fileNameKey;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);
    }

    protected function get_display_value()
    {
        return $this->default_display_value();
    }

    protected function default_display_value()
    {
        if (!$this->_value)
            $val = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
        else
            $val = $this->_value;
        return "<span class='view sxinput-file image'>
                    <img src='{$val}' alt='SXFile_Image' style='height: 70px;'>
                </span>";
    }

    protected function render_control()
    {
        return "<input id='{$this->_fieldName}'
                       name='{$this->_fieldName}'
                       class='sxinput-file popup-input file image'
                       type='file'
                       style='{$this->_styleString}'
                       data-type='file_image_db'
                       data-field-type='file'
                       data-required='{$this->_required}'
                       data-hint='{$this->_placeholder}'
                       accept='image/*'
                       {$this->_disabled}/>";
    }
}