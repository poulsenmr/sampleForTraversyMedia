<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:16 AM
 */

namespace SXSuite;

define("CBG_HORIZONTAL", "horizontal");
define("CBG_VERTICAL", "vertical");
define("CBG_GRID_COLS", "gridcols");

class CheckboxGroup_DB extends Lookup
{
    private $___table;
    private $___column_value;
    private $___column_display;
    private $___column_tooltip;
    private $___filter_str;
    private $___column_order_by;
    private $___option_orientation;
    private $___grid_columns;


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


        $this->___option_orientation = isset($params['orientation'])
            ? (in_array($params['orientation'], [CBG_VERTICAL, CBG_HORIZONTAL, CBG_GRID_COLS]) ? $params['orientation'] : CBG_VERTICAL)
            : CBG_VERTICAL;

        $this->___grid_columns = isset($params['grid_columns'])
            ? $params['grid_columns']
            : 3;

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
        return preg_replace("/\{self_value}\}/", $this->default_display_value(), $str);
    }

    protected function default_display_value()
    {
        $vals = explode(",", $this->_value);
        $options = \Database::query("
            SELECT
                {$this->___column_value} as [value],
                {$this->___column_display} as display,
                {$this->___column_tooltip} as title
            FROM {$this->___table}
            WHERE {$this->___column_value} IN ($vals)
            {$this->___column_order_by}", RETURN_FLAT_ARRAY);

        return "<span>" . implode("</span>,<span>", $options) . "</span>";
    }

    protected function get_display_value()
    {
        if ($this->_render_control_with_view)
            return "<span class='view sxinput-checkbox-group'><span class='hidden'>{$this->render_control()}</span><span>{$this->default_display_value()}</span></span>";
        else
            return "<span class='view sxinput-checkbox-group'>{$this->default_display_value()}</span>";
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();

        $optCount = 0;
        $options = \Database::query("
            SELECT
                {$this->___column_value} as [value],
                {$this->___column_display} as display,
                {$this->___column_tooltip} as title
            FROM {$this->___table}
            {$this->___filter_str}
            {$this->___column_order_by}");

        $checkboxesHTML = "";
        foreach ($options as $option) {
            $optCount++;

            $checked = in_array($option['value'], explode(",", $this->_value)) ? "checked" : "";


            $checkboxesHTML .= "<span class='option'>
                                    <input id='{$this->_fieldName}_{$optCount}'
                                           name='{$this->_fieldName}'
                                           class='sxinput-dropdown-db popup-input checkbox-group'
                                           data-field-type='checkbox-group'
                                           data-fieldname='{$this->_fieldName}'
                                           data-lookup-table='{$this->___table}'
                                           data-lookup-keyCol='{$this->___column_value}'
                                           data-type='checkbox-group'
                                           data-required='{$this->_required}'
                                           data-hint='{$this->_placeholder}'
                                           type='checkbox'
                                           value='{$option['value']}'
                                           title='{$option['title']}'
                                           style='{$this->_styleString}'
                                           $checked
                                           {$this->_disabled}>
                                    <label for='{$this->_fieldName}_{$optCount}' title='{$option['title']}'>{$option['display']}</label>
                                </span>";
        }

        $gridcolumns = $this->___option_orientation === CBG_GRID_COLS ? "grid-template-columns: repeat({$this->___grid_columns}, auto)" : "";
        return "<div class='checkbox-group cbg-{$this->___option_orientation}' style='{$gridcolumns}'>{$checkboxesHTML}</div>{$field_history}";
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

            $vals = explode(",", $deltas[$this->_fieldName]);
            $options = \Database::query("
            SELECT
                {$this->___column_value} as [value],
                {$this->___column_display} as display,
                {$this->___column_tooltip} as title
            FROM {$this->___table}
            WHERE {$this->___column_value} IN ({$deltas[$this->_fieldName]})
            {$this->___column_order_by}", RETURN_FLAT_ARRAY);

            $vals = [];
            foreach($options as $option)
                $vals[] = $option['display'];

            $value = "<span>" . implode("</span>,<span>", $vals) . "</span>";

            $html .= "<div>
                         <span>" . \Traxxus::valueOf($delta_record, 'changedDate') . "</span>
                         <span>{$value}</span>
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