<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:13 AM
 */

namespace SXSuite;
abstract class Field extends SXSuite
{
    protected $_fieldName;
    protected $_value;
    protected $_required;
    protected $_disabled;
    protected $_ignore_on_submit;
    protected $_placeholder;
    protected $_custom_display_str;
    protected $_render_control_with_view;
    protected $_show_delta;
    protected $_delta_table;
    protected $_target_table;
    protected $_record_id;
    protected $_dependency;
    protected $_dependency_type;
    protected $_dependency_values;
    protected $_dependency_column;
    protected $_parent_value;
    protected $_priority_field;
    protected $_enable_on_dependency_match;
    protected $_hidden;
    protected $_styleString;

    protected $_width;

    protected $_data_attributes;

    function __construct($params, $record = [])
    {
        parent::__construct($record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (!isset($params['fieldName']))
            die("no field name set");

        $this->_fieldName = $params['fieldName'];

        $this->_required = isset($params['required']) && $params['required'] == true ? "true" : "false";
        $this->_placeholder = isset($params['placeholder']) ? $params['placeholder'] : false;
        $this->_disabled = isset($params['disabled']) && $params['disabled'] == true ? "disabled" : "";
        $this->_ignore_on_submit = isset($params['ignore_on_submit']) && $params['ignore_on_submit'] == true ? "ignored" : "";
        $this->_dependency = isset($params['dependency']) && $params['dependency'] ? $params['dependency'] : false;
        $this->_dependency_type = isset($params['dependency_type']) && $params['dependency_type'] ? $params['dependency_type'] : false;
        $this->_dependency_values = isset($params['dependency_values']) && $params['dependency_values'] ? $params['dependency_values'] : false;
        $this->_dependency_column = isset($params['dependency_column']) && $params['dependency_column'] ? $params['dependency_column'] : false;
        $this->_priority_field = isset($params['priority_field']) && $params['priority_field'] ? $params['priority_field'] : false;
        $this->_enable_on_dependency_match = isset($params['enableOnDependencyMatch']) && $params['enableOnDependencyMatch'] ? $params['enableOnDependencyMatch'] : false;
        $this->_width = isset($params['width']) ? $params['width'] : false;
        $this->_hidden = isset($params['hidden']) ? $params['hidden'] : false;
        $this->_styleString = isset($params['styleString']) ? $params['styleString'] : false;


        $this->_value = isset($params['value']) && $params['value'] !== null
            ? $params['value']
            : (isset($this->record[$this->_fieldName])
                ? $this->record[$this->_fieldName]
                : null
            );

        if (isset($params['deltas'])) {
            $this->_record_id = isset($params['deltas']['recordID'])
                ? $params['deltas']['recordID']
                : false;
            $this->_delta_table = isset($params['deltas']['delta_table'])
                ? $params['deltas']['delta_table']
                : false;
            $this->_target_table = isset($params['deltas']['target_table'])
                ? $params['deltas']['target_table']
                : false;

            $this->_show_delta = isset($params['deltas']['show']) && $params['deltas']['show'] === true;
        }


        $this->_custom_display_str = isset($params['custom_display_str']) && $params['custom_display_str']
            ? $params['custom_display_str']
            : null;

        $this->_render_control_with_view = isset($params['render_control_with_view'])
            ? $params['render_control_with_view']
            : false;
    }

    public function loadFromParentObj($parentObj)
    {
        $objValues = get_object_vars($parentObj); // return array of object values
        foreach ($objValues AS $key => $value) {
            $this->$key = $value;
        }
    }

    public function set_record($record)
    {
        $this->record = $record;
    }

    public function render()
    {
        return $this->viewMode()
            ? $this->render_view()
            : $this->render_control();
    }

    protected function render_view()
    {
        if ($this->_custom_display_str)
            return $this->custom_display_value();
        else
            return $this->_show_delta
                ? $this->get_display_value_with_history()
                : $this->get_display_value();
    }

    abstract protected function custom_display_value();

    protected function get_display_value_with_history()
    {
        $field_history = $this->getHistory();
        $val = $this->get_display_value();

        return $val . $field_history;
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

            $html .= "<div>
                         <span>" . \Traxxus::valueOf($delta_record, 'changedDate') . "</span>
                         <span>{$deltas[$this->_fieldName]}</span>
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

    abstract protected function get_display_value();

    abstract protected function render_control();
}