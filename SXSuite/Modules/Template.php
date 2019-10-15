<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:11 AM
 */

namespace SXSuite;

class Template extends SXSuite
{

    const SRC_FILE = "file";
    const SRC_RAW_HTML = "raw";

    private static $instance;
    public $loaded_templates = [];
    public $error = false;
    public $errorMessage = false;
    private $file;
    private $template_string;
    private $data = [];
    private $templateData = [];
    private $stylesheets = [];
    private $scripts = [];
    private $revision;

    function __construct(string $file = null, $record = [], $src = self::SRC_FILE)
    {
        if ($file) {
            parent::__construct($record);
            $instance = self::get_instance();
            $this->revision = "1.0";

            if (!$file)
                die ("template file or raw HTML reference must be provided");

            if ($src === self::SRC_RAW_HTML) {
                $this->template_string = $file;
            } else {
                if (isset($instance->loaded_templates[$file]))
                    $this->template_string = $instance->loaded_templates[$file];
                else {
                    if (file_exists($file)) {
                        $this->template_string = file_get_contents($file);

                        $instance->loaded_templates[$file] = $this->template_string;
                    } else {
                        $this->template_string = "Template file {$file} does not exist or incorrect path provided";
                        $this->error = true;
                        $this->errorMessage = $this->template_string;
                    }
                }
            }
        }
    }

    private function get_instance()
    {
        if (!self::$instance)
            self::$instance = new Template();

        return self::$instance;
    }

    public static function render_immediate($file)
    {
        $template = new Template($file);
        return $template->render();
    }

    public function render($viewMode = false)
    {
        $contents = $this->template_string;

        $stylesheetHTML = "";
        foreach ($this->stylesheets as $stylesheet) {
            $stylesheetHTML .= "<link href='{$stylesheet}' rel='stylesheet' type='text/css'>";
        }
        $this->set("stylesheets", $stylesheetHTML);

        $scriptsHTML = "";
        foreach ($this->scripts as $script) {
            $scriptsHTML .= "<script src='{$script}?v={$this->revision}' type='text/javascript'></script>";
        }
        $this->set("scripts", $scriptsHTML);

        foreach ($this->templateData as $index => $value) {
            $value = \Traxxus\Misc::htmlspecialchars($value);
            $contents = preg_replace("/\[\{{$index}\}\]/", "{{$value}}", $contents);
        }

        foreach ($this->data as $index => $value) {
            switch (true) {
                case $value instanceof Field:
                case $value instanceof \SXInput:
                    $val = $this->curlyBracesToHTML($value->render($viewMode));
                    break;
                case isset($value['date']):
                    $val = date_create($value['date'])->format("m/d/Y");
                    break;
                default:
                    $val = $this->curlyBracesToHTML($value);
            }

            $contents = preg_replace("/\{" . $index . "\}/", $val, $contents);
        }

        $contents = preg_replace("/\{.*?\}/", "", $contents);
        $contents = preg_replace("/(\s|\r|\t|\n)/", " ", $contents);
        $contents = $this->htmlToCurlyBraces($contents);

        return $this->utf8ize($contents);
    }

    private function curlyBracesToHTML($str)
    {
        $str = str_replace("{", "&#123;", $str);
        $str = str_replace("}", "&#125;", $str);

        return $str;
    }

    private function htmlToCurlyBraces($str)
    {
        $str = str_replace("&#123;", "{", $str);
        $str = str_replace("&#125;", "}", $str);

        return $str;
    }

    private function utf8ize($d)
    {
        /**
         * utf8ize
         *
         * recursively converts an array to UTF8 encoding
         *
         * @d       various     type of paramter is checked on execution; if array, it will recursively run through this function until it encounters a string
         * @return  string      UTF8 encoded string
         */

        // check the type of $d
        if (is_array($d)) {
            // it's an array; recursively call this function again until a string is encountered
            foreach ($d as $k => $v) {
                $d[$k] = utf8ize($v);
            }
        } else if (is_string($d)) {
            // we've hit a string; utf8 encode it
            return utf8_encode($d);
        }

        // return the UTF8 encoded string
        return $d;
    }

    public function resetData()
    {
        $this->data = [];
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function set_template_field($key, $value)
    {
        $this->templateData[$key] = $value;
    }

    public function addField_Text($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for text control");
        if (!isset($params['fieldName']))
            die("missing field name for text control");

        $this->set($template_field, new Text([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function addField_Number($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for text control");
        if (!isset($params['fieldName']))
            die("missing field name for text control");

        $this->set($template_field, new Number([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "numbox_type" => isset($params['numbox_type']) ? $params['numbox_type'] : false,
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_column" => isset($params['dependency_column']) ? $params['dependency_column'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_Date($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for text control");
        if (!isset($params['fieldName']))
            die("missing field name for text control");

        $this->set($template_field, new Date([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['required']) ? $params['required'] === true : false,
            "ignore_on_submit" => isset($params['required']) ? $params['required'] === true : false,
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "picker_type" => isset($params['picker_type']) ? $params['picker_type'] : Date::BOOTSTRAP_DATEPICKER,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_Checkbox($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for checkbox control");
        if (!isset($params['fieldName']))
            die("missing field name for checkbox control");

        $this->set($template_field, new Checkbox([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_CheckboxGroup_DB($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for dropdown_db control");
        if (!isset($params['fieldName']))
            die("missing field name for dropdown_db control");
        if (!isset($params['table']))
            die("missing reference table for dropdown_db control");
        if (!isset($params['column_value']))
            die("missing value column for dropdown_db control");

        $this->set($template_field, new CheckboxGroup_DB([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "table" => $params['table'],
            "column_value" => $params['column_value'],
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "column_display" => isset($params['column_display']) ? $params['column_display'] : $params['column_value'],
            "column_tooltip" => isset($params['column_tooltip']) ? $params['column_tooltip'] : $params['column_value'],
            "filter_str" => isset($params['filter_str']) ? $params['filter_str'] : null,
            "column_order_by" => isset($params['column_order_by']) ? $params['column_order_by'] : (isset($params['column_display']) ? $params['column_display'] : $params['column_value']),
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "render_control_with_view" => isset($params['render_control_with_view']) ? $params['render_control_with_view'] === true : false,
            "orientation" => isset($params['orientation']) ? $params['orientation'] : CBG_GRID_COLS,
            "grid_columns" => isset($params['grid_columns']) ? $params['grid_columns'] : 3,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_LiveQuery($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for livequery control");
        if (!isset($params['fieldName']))
            die("missing field name for livequery control");
        if (!isset($params['table']))
            die("missing reference table for livequery control");
        if (!isset($params['column_value']))
            die("missing value column for livequery control");

        $this->set($template_field, new LiveQuery([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "table" => $params['table'],
            "column_value" => $params['column_value'],
            "column_search" => isset($params['column_search']) ? $params['column_search'] : $params['column_value'],
            "column_tooltip" => isset($params['column_tooltip']) ? $params['column_tooltip'] : $params['column_value'],
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : "Type to search...",
            "filter_str" => isset($params['filter_str']) ? $params['filter_str'] : null,
            "column_order_by" => isset($params['column_order_by']) ? $params['column_order_by'] : $params['column_value'],
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "include_please_select" => isset($params['include_please_select']) ? $params['include_please_select'] === true : false,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_Dropdown_DB($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for dropdown_db control");
        if (!isset($params['fieldName']))
            die("missing field name for dropdown_db control");
        if (!isset($params['table']))
            die("missing reference table for dropdown_db control");
        if (!isset($params['column_value']))
            die("missing value column for dropdown_db control");

        $this->set($template_field, new Dropdown_DB([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "table" => $params['table'],
            "column_value" => $params['column_value'],
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "column_display" => isset($params['column_display']) ? $params['column_display'] : $params['column_value'],
            "column_tooltip" => isset($params['column_tooltip']) ? $params['column_tooltip'] : $params['column_value'],
            "filter_str" => isset($params['filter_str']) ? $params['filter_str'] : null,
            "option_data_columns" => isset($params['option_data_columns']) ? $params['option_data_columns'] : null,
            "column_order_by" => isset($params['column_order_by']) ? $params['column_order_by'] : (isset($params['column_display']) ? $params['column_display'] : $params['column_value']),
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "include_please_select" => isset($params['include_please_select']) ? $params['include_please_select'] === true : false,
            "render_control_with_view" => isset($params['render_control_with_view']) ? $params['render_control_with_view'] === true : false,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "priority_field" => isset($params['priority_field']) ? $params['priority_field'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_Dropdown_FS($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for dropdown_db control");
        if (!isset($params['fieldName']))
            die("missing field name for dropdown_db control");
        if (!isset($params['src_dir']))
            die("missing source directory for dropdown_fs control");

        $this->set($template_field, new Dropdown_FS([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "src_dir" => $params['src_dir'],
            "data_attributes" => isset($params['data_attributes']) ? $params['data_attributes'] : [],
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "include_please_select" => isset($params['include_please_select']) ? $params['include_please_select'] === true : false,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_Radio_DB($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for radio_db control");
        if (!isset($params['fieldName']))
            die("missing field name for radio_db control");
        if (!isset($params['table']))
            die("missing reference table for radio_db control");
        if (!isset($params['column_value']))
            die("missing value column for radio_db control");

        $this->set($template_field, new Radio_DB([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "table" => $params['table'],
            "column_value" => $params['column_value'],
            "column_display" => isset($params['column_display']) ? $params['column_display'] : $params['column_value'],
            "column_tooltip" => isset($params['column_tooltip']) ? $params['column_tooltip'] : $params['column_value'],
            "filter_str" => isset($params['filter_str']) ? $params['filter_str'] : null,
            "column_order_by" => isset($params['column_order_by']) ? $params['column_order_by'] : $params['column_value'],
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "include_please_select" => isset($params['include_please_select']) ? $params['include_please_select'] === true : false,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_File($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for file control");
        if (!isset($params['fieldName']))
            die("missing field name for file control");

        $this->set($template_field, new File([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_TextArea($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for textarea control");
        if (!isset($params['fieldName']))
            die("missing field name for textarea control");

        $this->set($template_field, new TextArea([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_Image_DB($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for file control");
        if (!isset($params['fieldName']))
            die("missing field name for file control");

        $this->set($template_field, new File_Image_DB([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function addField_Signature($template_field, $params)
    {
        if (!$template_field)
            die("missing template field name for text control");
        if (!isset($params['fieldName']))
            die("missing field name for signature control");
        if (!isset($params['font_style_path']))
            die("missing font style path for signature control");


        $this->set($template_field, new Signature([
            "parentObj" => $this,
            "fieldName" => $params['fieldName'],
            "value" => isset($params['value']) ? $params['value'] : null,
            "custom_display_str" => isset($params['custom_display_str']) ? $params['custom_display_str'] : null,
            "height" => isset($params['height']) ? $params['height'] : 100,
            "width" => isset($params['width']) ? $params['width'] : 300,
            "font_style_path" => $params['font_style_path'],
            "required" => isset($params['required']) ? $params['required'] === true : false,
            "disabled" => isset($params['disabled']) ? $params['disabled'] === true : false,
            "ignore_on_submit" => isset($params['ignore_on_submit']) ? $params['ignore_on_submit'] === true : false,
            "placeholder" => isset($params['placeholder']) ? $params['placeholder'] : null,
            "deltas" => isset($params['deltas']) ? $params['deltas'] : false,
            "dependency" => isset($params['dependency']) ? $params['dependency'] : false,
            "dependency_type" => isset($params['dependency_type']) ? $params['dependency_type'] : false,
            "dependency_values" => isset($params['dependency_values']) ? $params['dependency_values'] : false,
            "enableOnDependencyMatch" => isset($params['enableOnDependencyMatch']) ? $params['enableOnDependencyMatch'] : false,
            "styleString" => isset($params['styleString']) ? $params['styleString'] : false,
            "hidden" => isset($params['hidden']) ? $params['hidden'] : false
        ], $this->record));
    }

    public function setArray($array)
    {
        foreach ($array as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    public function addCSS($cssPath, $ignoreVersion = false)
    {
        if ($ignoreVersion)
            $this->stylesheets[] = $cssPath;
        else
            $this->stylesheets[] = $cssPath . "?v={$this->revision}";
    }

    public function addCSSArr($cssArr)
    {
        foreach ($cssArr as $cssFile) {
            $this->stylesheets[] = $cssFile;
        }

        return $this;
    }

    public function addJS($jsPath)
    {
        $this->scripts[] = $jsPath;
    }

    public function addJSArr($jsArr)
    {
        foreach ($jsArr as $jsFile) {
            $this->scripts[] = $jsFile;
        }

        return $this;
    }

    public function dumpVariables()
    {
        foreach ($this->data as $index => $value) {
            echo("$index => $value<br>");
        }
    }
}