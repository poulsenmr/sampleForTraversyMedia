<?php
/**
 * Created by PhpStorm.
 * User: jeremyd
 * Date: 5/10/2018
 * Time: 9:15 AM
 */

namespace SXSuite;
abstract class Lookup extends Field
{
    protected $__include_please_select;

    function __construct($params, $record = [])
    {
        parent::__construct($params, $record);
        if (isset($params['parentObj']))
            $this->loadFromParentObj($params['parentObj']);

        $this->__include_please_select = isset($params['include_please_select']) && $params['include_please_select'] == true;
    }
}