<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:17 AM
 */

namespace SXSuite;
class File extends Field
{
    /***
     * do this shit later... it does not currently work =[
     */
    private $__fileName;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        $this->__fileName = isset($params['fileName']) ? $params['fileName'] : "";
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->get_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {
        return $this->__fileName;
    }

    protected function default_display_value()
    {
        return "<span class='view sxinput-file'>{$this->get_display_value()}</span>";
    }

    protected function render_control()
    {
        return "<input id='{$this->_fieldName}'
                       name='{$this->_fieldName}'
                       class='sxinput-file popup-input file'
                       data-field-type='file'
                       data-fieldname='{$this->_fieldName}'
                       type='file'
                       style='{$this->_styleString}'>";
    }
}