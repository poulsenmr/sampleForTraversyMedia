<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:13 AM
 */


namespace SXSuite;
class Text extends Field
{
    private $___hidden;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        $this->___hidden = isset($params['hidden']) ? $params['hidden'] === true : false;
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->default_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {
        return $this->_value;
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();
        $hidden_class = $this->___hidden ? "hidden" : "";

        return "<input id='{$this->_fieldName}'
                       name='{$this->_fieldName}'
                       class='sxinput sxinput-text popup-input text {$hidden_class}'
                       type='text'
                       value='{$this->_value}'
                       data-required='{$this->_required}'
                       data-type='text'
                       data-field-type='text'
                       data-fieldname='{$this->_fieldName}'
                       placeholder='{$this->_placeholder}'
                       style='{$this->_styleString}'
                       {$this->_disabled}>
                {$field_history}";
    }
}