<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:15 AM
 */

namespace SXSuite;

class Date extends Field
{
    const BOOTSTRAP_DATEPICKER = "bootstrap";
    const SXINPUT_DATEPICKER = "sxinput";

    private $picker_type = self::BOOTSTRAP_DATEPICKER;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (isset($params['picker_type'])) {
            if (in_array($params['picker_type'], [self::BOOTSTRAP_DATEPICKER, self::SXINPUT_DATEPICKER]))
                $this->picker_type = $params['picker_type'];
        }
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->get_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {

        return \Traxxus::valueOf($this->_value, true);
    }

    protected function default_display_value()
    {
        return "<span class='view sxinput-date'>{$this->get_display_value()}</span>";
    }

    protected function render_control()
    {
        $field_history = $this->getHistory();

        switch ($this->picker_type) {
            case self::SXINPUT_DATEPICKER:
                $wrapper = "sxinput-wrapper date";
                break;
            case self::BOOTSTRAP_DATEPICKER:
            default:
                $wrapper = "inline-date datepickerwrapper";
                break;
        }

        return "<div class='{$wrapper}'>
                    <span>
                        <input id='{$this->_fieldName}'
                               name='{$this->_fieldName}'
                               class='sxinput-date popup-input datepicker'
                               type='text'
                               value='{$this->get_display_value()}'
                               data-field-type='date'
                               data-fieldname='{$this->_fieldName}'
                               data-required='{$this->_required}'
                               data-type='date'
                               placeholder=\"{$this->_placeholder}\"
                               style='{$this->_styleString}'
                               data-dependency='{$this->_dependency}'
                               data-dependency-type='{$this->_dependency_type}'
                               data-dependency-values='{$this->_dependency_values}'
                               data-enable-on-dependency-match='{$this->_enable_on_dependency_match}'
                               {$this->_disabled}>
                    </span>
                </div>
                {$field_history}";
    }
}