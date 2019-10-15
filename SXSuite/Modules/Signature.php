<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:18 AM
 */

namespace SXSuite;
class Signature extends Field
{
    protected $__placeholder;
    private $__height;
    private $__width;
    private $__font_stlyle_path;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        if (!isset($params['font_style_path']))
            return;
        else
            $this->__font_stlyle_path = $params['font_style_path'];

        $this->__height = isset($params['height']) ? $params['height'] : 100;
        $this->__width = isset($params['width']) ? $params['width'] : 300;
    }

    protected function custom_display_value()
    {
        return preg_replace("/\{value\}/", $this->default_display_value(), $this->_custom_display_str);
    }

    protected function get_display_value()
    {
        if (isset($this->record[$this->_fieldName])) {
            $raw = $this->record[$this->_fieldName];
            if ($this->is_base64($raw))
                $data = $raw;
            else {
                $imgdata = base64_encode(hex2bin(ltrim($raw, "0x")));

                $f = finfo_open();
                $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);

                $data = "data:image/{$mime_type};base64,{$imgdata}";
            }
        } else
            $data = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";

        return "<span class='view sxinput-signature'><img width='{$this->__width}' height='{$this->__height}' src='{$data}'</span>";
    }

    private function is_base64($s)
    {
        if (substr($s, 0, 2) === "0x")
            return false;
        else
            return true;
    }

    protected function render_control()
    {
        $typedSignatureInput = new Text([
            "fieldName" => "ignore_signature_text",
            "placeholder" => "Type name here to sign",
            "ignore_on_submit" => true,
        ], $this->record);
        $typedSignatureFontSelection = new Dropdown_FS([
            "fieldName" => "ignore_signature_font",
            "src_dir" => "/inetpub/deployed-sites/TRAXXUS_ROOT/include/custom/fonts/signature_fonts",
            "data_attributes" => [
                "font-family" => "{filename}"
            ],
            "value" => "Pretty-Pen-Regular.WOFF",
            "ignore_on_submit" => true,
            "placeholder" => "Signature Font",
            "include_please_select" => false
        ], $this->record);
        return "<div class='sxinput-signature'>
                    <div class='sxinput-tabs'>
                        <span class='sxinput-tab active' data-target='tab1'>Sign with Mouse/Finger</span>
                        <span class='sxinput-tab' data-target='tab2'>Sign by typing</span>
                    </div>
                    <div class='sxinput-tab-body tab1 active'>
                        <div class='m-signature-pad--body' id='signature-pad'>
                            <div class='signature-controls'>
                                <div class='clear' title='Clear Canvas'></div>
                            </div>
                            <div class='m-signature-pad-body'>
                                <canvas width='{$this->__width}' height='{$this->__height}' style='touch-action: none'></canvas>
                            </div>
                            <div class='m-signature-pad--footer'>
                                <div class='description'>{$this->_placeholder}</div>
                            </div>
                        </div>
                    </div>
                    <div class='sxinput-tab-body tab2'>
                        <div>
                            {$typedSignatureInput->render()}
                            {$typedSignatureFontSelection->render()}
                        </div>
                        <div>
                            <div class='sxinput-typed-signature'></div>
                        </div>
                    </div>
                </div>
                <input id='{$this->_fieldName}'
                       name='{$this->_fieldName}'
                       class='signature popup-input'
                       type='hidden'
                       style='{$this->_styleString}'
                       data-field-type='signature'
                       data-fieldname='{$this->_fieldName}'
                       data-type='signature'
                       data-required='{$this->_required}'>
                ";
    }
}