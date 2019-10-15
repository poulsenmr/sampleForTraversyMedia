<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:16 AM
 */

namespace SXSuite;
class LiveQuery extends Lookup
{
    private $___table;
    private $___column_value;
    private $___column_search;
    private $___column_tooltip;
    private $___filter_str;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (!isset($params['table']))
            die("no table specified for live query source");
        if (!isset($params['column_value']))
            die("no value column specified for live query value");

        $this->___table = $params['table'];
        $this->___column_value = $params['column_value'];

        $this->___column_search = isset($params['column_search'])
            ? $params['column_search']
            : $params['column_value'];

        $this->___column_tooltip = isset($params['column_tooltip'])
            ? $params['column_tooltip']
            : $params['column_value'];

        $this->___filter_str = isset($params['filter_str'])
            ? $params['filter_str']
            : false;

        $this->_placeholder = isset($params['placeholder'])
            ? $params['placeholder']
            : "Type to search...";
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->get_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {
        if (isset($this->record[$this->_fieldName]))
            return \Database::lookup("SELECT {$this->___column_search} FROM {$this->___table} WHERE {$this->___column_value}='{$this->record[$this->_fieldName]}'");
        else
            return "";
    }

    protected function default_display_value()
    {
        return "<span class='view sxinput-liveQuery'>{$this->get_display_value()}</span>";
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();

        $filterText = $this->___filter_str ? "data-livequery-filter-text='{$this->___filter_str}'" : "";
        $tooltipColumn = $this->___column_tooltip ? "data-tooltip-column=\"{$this->___column_tooltip}\"" : "";
        $selectedValueAttr = $this->_value ? "data-selected-value='{$this->_value}'" : "";
        $matched = $this->get_display_value() ? "match" : "";

        return "<input id=\"{$this->_fieldName}\"
                       name=\"{$this->_fieldName}\"
                       type=\"text\"
                       class=\"sxinput sxinput-liveQuery popup-input liveQuery {$matched}\"
                       value='{$this->get_display_value()}'
                       style='{$this->_styleString}'
                       {$selectedValueAttr}
                       data-field-type='liveQuery'
                       data-fieldname=\"{$this->_fieldName}\"
                       data-required=\"{$this->_required}\"
                       data-fieldtype=\"liveQuery\"
                       data-livequery-source-table=\"{$this->___table}\"
                       data-livequery-searchcol=\"{$this->___column_search}\"
                       data-livequery-value-column=\"{$this->___column_value}\"
                       {$tooltipColumn}
                       {$filterText}
                       {$this->_disabled}
                       placeholder='{$this->_placeholder}'
                       autocomplete='off'>
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

            $option = \Database::lookup("SELECT {$this->___column_search} FROM {$this->___table} WHERE {$this->___column_value}='{$deltas[$this->_fieldName]}'");

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