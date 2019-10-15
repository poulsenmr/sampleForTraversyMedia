<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:16 AM
 */

namespace SXSuite;
class Dropdown_DB extends Lookup
{
    private $___table;
    private $___column_value;
    private $___column_display;
    private $___column_tooltip;
    private $___filter_str;
    private $___column_order_by;
    private $___option_data_columns;
    private $___hidden;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (!isset($params['table']))
            die("no table specified for dropdown source");
        if (!isset($params['column_value']))
            die("no value column specified for dropdown");

        $this->___table = $params['table'];
        $this->___column_value = $params['column_value'];

        $this->___column_display = isset($params['column_display'])
            ? $params['column_display']
            : $params['column_value'];

        $this->___column_tooltip = isset($params['column_tooltip'])
            ? $params['column_tooltip']
            : $params['column_value'];

        $this->___filter_str = isset($params['filter_str'])
            ? $params['filter_str']
            : false;

        $this->___column_order_by = isset($params['column_order_by'])
            ? "ORDER BY {$params['column_order_by']}"
            : "ORDER BY {$params['column_display']}";

        $this->___hidden = isset($params['hidden']) ? $params['hidden'] === true : false;

        if (isset($params['option_data_columns']) && $params['option_data_columns']) {
            if (gettype($params['option_data_columns']) != "array")
                die("option data columns must be provided via an array of column names");
            $this->___option_data_columns = $params['option_data_columns'];
        } else
            $this->___option_data_columns = [];
    }

    protected function custom_display_value()
    {
        $str = $this->_custom_display_str;
        $selectedOption = \Database::query("
            SELECT *
            FROM {$this->___table}
            WHERE {$this->___column_value}={$this->_value}", RETURN_SINGLE_ARRAY);

        if ($selectedOption) {
            foreach ($selectedOption as $key => $val) {
                $pattern = "option_{$key}";
                $str = preg_replace("/\{" . $pattern . "\}/", $val, $str);
            }
        }

        $str = preg_replace("/\{.*?\}/", "", $str);
        return preg_replace("/\{self_value}\}/", $this->default_display_value(), $str);
    }

    protected function default_display_value()
    {
        return \Database::lookup("SELECT {$this->___column_display} FROM {$this->___table} WHERE {$this->___column_value}={$this->record[$this->_fieldName]}");
    }

    protected function get_display_value()
    {
        if ($this->_render_control_with_view)
            return "<span class='view sxinput-dropdown'><span class='hidden'>{$this->render_control()}</span><span>{$this->default_display_value()}</span></span>";
        else
            return "<span class='view sxinput-dropdown'>{$this->default_display_value()}</span>";
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();

        if ($this->_dependency) {
            $options = \Database::query("
            SELECT
                {$this->___column_value} as [value],
                {$this->___column_display} as display,
                {$this->___column_tooltip} as title
            FROM {$this->___table}
            WHERE {$this->_dependency} IN {$this->_dependency_values}
            {$this->___filter_str}
            {$this->___column_order_by}");
        } else {
            $options = \Database::query("
            SELECT
                {$this->___column_value} as [value],
                {$this->___column_display} as display,
                {$this->___column_tooltip} as title
            FROM {$this->___table}
            {$this->___filter_str}
            {$this->___column_order_by}");
        }

        $optionsHTML = "";
        if ($this->__include_please_select)
            $optionsHTML .= "<option value>Please Select...</option>";
        foreach ($options as $option) {
            $selected = $this->_value == $option['value'] ? "selected" : "";

            $dataAttributes = "";
            if ($this->___option_data_columns) {
                foreach ($this->___option_data_columns as $data_column) {
                    $colVal = \Database::lookup("
                              SELECT {$data_column}
                              FROM {$this->___table}
                              WHERE {$this->___column_value}={$option['value']}");
                    $dataAttributes .= "data-{$data_column}='$colVal' ";
                }
            }

            $optionsHTML .= "<option value='{$option['value']}'
                                     title='{$option['title']}'
                                     {$dataAttributes}
                                     $selected>{$option['display']}</option>";
        }
        $hidden_class = $this->___hidden ? "hidden" : "";

        return "<select id='{$this->_fieldName}'
                        name='{$this->_fieldName}'
                        class='sxinput-dropdown-db popup-input dropdown {$hidden_class}'
                        style='{$this->_styleString}'
                        data-field-type='dropdown'
                        data-fieldname='{$this->_fieldName}'
                        data-lookup-table='{$this->___table}'
                        data-lookup-keyCol='{$this->___column_value}'
                        data-type='dropdown'
                        data-required='{$this->_required}'
                        data-hint='{$this->_placeholder}'
                        data-priority-field='{$this->_priority_field}'
                        data-dependency='{$this->_dependency}'
                        data-dependency-type='{$this->_dependency_type}'
                        {$this->_disabled}>$optionsHTML</select>
                {$field_history}";
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

            $option = \Database::lookup("SELECT {$this->___column_display} FROM {$this->___table} WHERE {$this->___column_value}={$deltas[$this->_fieldName]}");

            $html .= "<div>
                         <span>" . \Traxxus::valueOf($delta_record, 'changedDate') . "</span>
                         <span>{$option}</span>
                         <span>{$delta_record['changedBy']}</span>
                      </div>";
        }
        if (!$html)
            return "";

        $html = "<div class='field_history'>
                    <div>
                        <div class='field_history_header'>
                            <span>Date Changed</span>
                            <span>Previous Value</span>
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