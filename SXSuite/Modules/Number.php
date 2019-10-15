<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:14 AM
 */

namespace SXSuite;
class Number extends Field
{
    private $__numbox_type;
    private $__numbox_class;
    private $__numbox_attr;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (isset($params['numbox_type'])) {
            $this->__numbox_type = $params['numbox_type'];
            $this->__numbox_class = "numbox";
            $this->__numbox_attr = "data-numboxtype='{$params['numbox_type']}'";
        } else {
            $this->__numbox_type = false;
            $this->__numbox_class = "";
            $this->__numbox_attr = "";
        }
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->get_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {
        setlocale(LC_MONETARY, 'en_US.UTF-8');
        return !$this->_value && $this->useNAforBlankValues()
            ? "NA"
            : ($this->__numbox_type == "usd"
                ? \number_format($this->_value, 2)
                : $this->_value);
    }

    protected function default_display_value()
    {
        return "<span class='view sxinput-number'>{$this->get_display_value()}</span>";
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();

        $fieldType = $this->__numbox_type ? "text" : "number";
        return "<input id='{$this->_fieldName}'
                       name='{$this->_fieldName}'
                       class='sxinput-number popup-input text {$this->__numbox_class}'
                       type='{$fieldType}'
                       value='{$this->_value}'
                       data-field-type='number'
                       data-fieldname='{$this->_fieldName}'
                       data-required='{$this->_required}'
                       data-type='number'
                       placeholder='{$this->_placeholder}'
                       style='{$this->_styleString}'
                       data-dependency='{$this->_dependency}'
                       data-dependency-type='{$this->_dependency_type}'
                       data-dependency-column='{$this->_dependency_column}'
                       {$this->__numbox_attr}
                       {$this->_disabled}>
               {$field_history}";
    }
}