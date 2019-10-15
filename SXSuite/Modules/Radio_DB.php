<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:17 AM
 */

namespace SXSuite;
class Radio_DB extends Lookup
{
    private $___table;
    private $___column_value;
    private $___column_display;
    private $___column_tooltip;
    private $___filter_str;
    private $___column_order_by;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (!isset($params['table']))
            die("no table specified for radio source");
        if (!isset($params['column_value']))
            die("no value column specified for radio");

        $this->___table = $params['table'];
        $this->___column_value = $params['column_value'];

        $this->___column_display = isset($params['column_display'])
            ? $params['column_display']
            : $params['column_value'];

        $this->___column_tooltip = isset($params['column_tooltip'])
            ? $params['column_tooltip']
            : $params['column_value'];

        $this->___column_tooltip = isset($params['filter_str'])
            ? $params['filter_str']
            : false;

        $this->___column_order_by = isset($params['column_order_by'])
            ? $params['column_order_by']
            : false;
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->get_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {
        return \Database::lookup("SELECT {$this->___column_display} FROM {$this->___table} WHERE {$this->___column_value}='{$this->record[$this->_fieldName]}'");
    }

    protected function render_control()
    {
        $options = \Database::query("
            SELECT
              {$this->___column_value} as [value],
              {$this->___column_display} as display,
              {$this->___column_tooltip} as title
            FROM {$this->___table}
            {$this->___filter_str}
            {$this->___column_order_by}");

        $optionsHTML = "";
        foreach ($options as $option) {
            $optionsHTML .= "<input id='{$this->_fieldName}'
                                    type='radio'
                                    class='sxinput-radio popup-input radio'
                                    name='{$this->_fieldName}'
                                    style='{$this->_styleString}'
                                    data-field-type='radio'
                                    data-required='{$this->_required}'
                                    data-fieldname='{$this->_fieldName}'
                                    value='{$option['value']}'
                                    data-type='radio'
                                    data-display-val='{$option['display']}'>";
        }
        return "<div class='sxinput-radio-wrapper'>{$optionsHTML}</div>";
    }
}