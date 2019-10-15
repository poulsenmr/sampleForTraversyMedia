<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:16 AM
 */

namespace SXSuite;

class Dropdown_FS extends Lookup
{
    private $___src_dir;
    private $___data_attributes;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (!isset($params['src_dir']))
            die("no source directory specified for dropdown source");
        if (!is_dir($params['src_dir']))
            die("invalid source directory specified for file system based dropdown ({$params['src_dir']})");

        $this->___src_dir = $params['src_dir'];

        if (isset($params['data_attributes']))
            $this->___data_attributes = $params['data_attributes'];
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->get_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {
        return $this->_value;
    }

    protected function default_display_value()
    {
        return "<span class='view sxinput-dropdown'>{$this->get_display_value()}</span>";
    }

    protected function render_control()
    {
        $files = scandir($this->___src_dir);

        $optionsHTML = "";
        if ($this->__include_please_select)
            $optionsHTML .= "<option value>Please Select...</option>";
        foreach ($files as $file) {
            $selected = $file === $this->_value
                ? "selected"
                : "";
            if ($file !== "." && $file !== "..") {
                $data_attributes = "";
                $filename = pathinfo("{$this->___src_dir}/$file", PATHINFO_FILENAME);
                if ($this->___data_attributes && gettype($this->___data_attributes) == "array") {
                    foreach ($this->___data_attributes as $attributeName => $attributeVal) {
                        $attributeVal = preg_replace("/\{filename\}/", $filename, $attributeVal);
                        $data_attributes .= "data-{$attributeName}='{$attributeVal}' ";
                    }
                }
                $optionsHTML .= "<option value='$file' $selected $data_attributes>$filename</option>";
            }
        }

        return "<select id='{$this->_fieldName}'
                        name='{$this->_fieldName}'
                        class='sxinput-dropdown-fs popup-input dropdown'
                        style='{$this->_styleString}'
                        data-field-type='dropdown'
                        data-fieldname='{$this->_fieldName}'
                        data-type='dropdown'
                        data-required='{$this->_required}'
                        data-hint='{$this->_placeholder}'
                        {$this->_disabled}>$optionsHTML</select>";
    }
}