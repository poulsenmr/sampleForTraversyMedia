<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:17 AM
 */

namespace SXSuite;
class TextArea extends Field
{
    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->default_display_value(), $this->_custom_display_str);
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();
        $field_width_style = $this->_width ? "width: {$this->_width}px" : "";
        return "{$field_history}
                <textarea id='{$this->_fieldName}'
                          name='{$this->_fieldName}'
                          class='sxinput sxinput-textarea popup-input textarea'
                          placeholder='{$this->_placeholder}'
                          data-field-type='textarea'
                          data-fieldname='{$this->_fieldName}'
                          data-required='{$this->_required}'
                          style='{$field_width_style} {$this->_styleString}'>
                                {$this->get_display_value()}                          
                </textarea>";
    }

    protected function get_display_value()
    {
        return $this->_value;
    }
}