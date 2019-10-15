<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 2:06 PM
 */

namespace SXSuite;
class Checkbox extends Field
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

    protected function default_display_value()
    {
        return $this->_value;
    }

    protected function get_display_value()
    {
        return $this->record[$this->_fieldName] ? "<i class=\"fa fa-check-square-o\"></i>" : "<i class=\"fa fa-square-o\"></i>";
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();

        if (isset($this->record[$this->_fieldName]) && $this->record[$this->_fieldName] == 1)
            $checked = "checked";
        else
            $checked = "";
        return "{$field_history}
                <input id='{$this->_fieldName}'
                       name='{$this->_fieldName}'
                       class='sxinput-checkbox popup-input checkbox'
                       type='checkbox'
                       value='{$this->_value}'
                        data-field-type='checkbox'
                       data-fieldname='{$this->_fieldName}'
                       data-required='{$this->_required}'
                       data-type='text'
                       placeholder='{$this->_placeholder}'
                       style='{$this->_styleString}'
                       {$checked}
                       {$this->_disabled}>";
    }

    public function getHistory()
    {
        if (!$this->_show_delta || !$this->_delta_table || !$this->_record_id)
            return "";

        // get the history records (hope this works)
        $delta_records = \Database::query("SELECT * FROM {$this->_delta_table} WHERE [table]='{$this->_target_table}' AND recordID={$this->_record_id} ORDER BY deltaID DESC");
        $html = "";
        foreach ($delta_records as $delta_record) {
            $deltas = json_decode($delta_record['delta'], true);
            if (!array_key_exists($this->_fieldName, $deltas))
                continue;

            $text = $deltas[$this->_fieldName] ? "Checked" : "Unchecked";

            $html .= "<div>
                         <span>" . \Traxxus::valueOf($delta_record, 'changedDate') . "</span>
                         <span>{$text}</span>
                         <span>{$delta_record['changedBy']}</span>
                      </div>";
        }
        if (!$html)
            return "";

        $viewMode = $this->viewMode() ? "data-view-mode" : "";
        $html = "<div class='field_history checkbox' {$viewMode}>
                    <div>
                        <div class='field_history_header'>
                            <span>Date Changed</span>
                            <span>Previous State</span>
                            <span>Changed By</span>
                        </div>
                        <div class='field_history_values'>
                            {$html}
                        </div>
                        
                    </div>
                 </div>";

        return $html;
    }
}