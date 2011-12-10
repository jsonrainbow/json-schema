<?php

/*
 * This file is part of JsonSchema.
 *
 * (c) Bruno Prieto Reis <bruno.p.reis@gmail.com>, Gradua Networks
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

class Validator
{
    public $checkMode = self::CHECK_MODE_NORMAL;
    private $errors = array();

    const CHECK_MODE_NORMAL = 1;
    const CHECK_MODE_TYPE_CAST = 2;

    /**
     * Validates a php object against a schema. Both the php object and the schema
     * are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in
     * http://json-schema.org
     *
     * @param \stdClass $instance
     * @param \stdClass $schema
     * @return unknown
     */
    public function validate($instance, $schema = null)
    {
        $this->errors = array();

        $_changing = false;

        // verify passed schema
        if ($schema) {
            $this->checkProp($instance, $schema,'','', $_changing);
        }
        // verify "inline" schema
        $propName = '$schema';
        if (!$_changing && isset($instance->$propName)) {
            $this->checkProp($instance, $instance->$propName,'','', $_changing);
        }
        // show results
        $obj = new \stdClass();
        $obj->valid = ! ((boolean)count($this->errors));
        $obj->errors = $this->errors;
        return $obj;
    }

    protected function incrementPath($path, $i)
    {
        if ($path !== '') {
            if (is_int($i)) {
                $path .= '['.$i.']';
            } else if ($i == '') {
                $path .= '';
            } else {
                $path .= '.'.$i;
            }
        } else {
            $path = $i;
        }
        return $path;
    }

    protected function checkArray($value, $schema, $path, $i, $_changing)
    {
        //verify items
        if (isset($schema->items)) {
            //tuple typing
            if (is_array($schema->items)) {
                foreach ($value as $k=>$v) {
                    if (array_key_exists($k, $schema->items)) {
                        $this->checkProp($v, $schema->items[$k], $path, $k, $_changing);
                    }
                    else {
                        // aditional array properties
                        if (array_key_exists('additionalProperties', $schema)) {
                            if ($schema->additionalProperties === false) {
                                $this->adderror(
                                    $path,
                                    'The item '.$i.'['.$k.'] is not defined in the objTypeDef and the objTypeDef does not allow additional properties'
                                );
                            }
                            else {
                                $this->checkProp($v, $schema->additionalProperties, $path, $k, $_changing);
                            }
                        }
                    }
                }//foreach ($value as $k=>$v) {
                // treat when we have more schema definitions than values
                for ($k = count($value); $k < count($schema->items); $k++) {
                    $this->checkProp(
                        new Undefined(),
                        $schema->items[$k], $path, $k, $_changing
                    );
                }
            }
            // just one type definition for the whole array
            else {
                foreach ($value as $k=>$v) {
                    $this->checkProp($v, $schema->items, $path, $k, $_changing);
                }
            }
        }
        // verify number of array items
        if (isset($schema->minItems) && count($value) < $schema->minItems) {
            $this->adderror($path,"There must be a minimum of " . $schema->minItems . " in the array");
        }
        if (isset($schema->maxItems) && count($value) > $schema->maxItems) {
            $this->adderror($path,"There must be a maximum of " . $schema->maxItems . " in the array");
        }
    }

    protected function checkProp($value, $schema, $path, $i = '', $_changing = false)
    {
        if (!is_object($schema)) {
                return;
        }
        $path = $this->incrementPath($path, $i);
        // verify readonly
        if ($_changing && $schema->readonly) {
            $this->adderror($path,'is a readonly field, it can not be changed');
        }
        // I think a schema cant be an array, only the items property
        /*if (is_array($schema)) {
            if (!is_array($value)) {
                return array(array('property'=>$path,'message'=>'An array tuple is required'));
            }
            for ($a = 0; $a < count($schema); $a++) {
                $this->errors = array_merge(
                    $this->errors,
                    $this->checkProp($value->$a, $schema->$a, $path, $i, $_changing)
                );
                return $this->errors;
            }
        }*/
        // if it extends another schema, it must pass that schema as well
        if (isset($schema->extends)) {
            $this->checkProp($value, $schema->extends, $path, $i, $_changing);
        }
        // verify required values
        if (is_object($value) && $value instanceOf Undefined) {
            if (isset($schema->required) && $schema->required) {
                $this->adderror($path,"is missing and it is required");
            }
        }
        // normal verifications
        else {
            $this->errors = array_merge(
                $this->errors,
                $this->checkType( isset($schema->type) ? $schema->type : null , $value, $path)
            );
        }
        if (array_key_exists('disallow', $schema)) {
            $errorsBeforeDisallowCheck = $this->errors;
            $response = $this->checkType($schema->disallow, $value, $path);
            if (
                ( count($errorsBeforeDisallowCheck) == count($this->errors) )  &&
                !count($response)
            ) {
                $this->adderror($path," disallowed value was matched");
            }
            else {
                $this->errors = $errorsBeforeDisallowCheck;
            }
        }
        //verify the itens on an array and min and max number of items.
        if (is_array($value)) {
            if (
                $this->checkMode == $this::CHECK_MODE_TYPE_CAST &&
                $schema->type == 'object'
            ) {
                $this->checkObj(
                    $value,
                    $schema->properties,
                    $path,
                    isset($schema->additionalProperties) ? $schema->additionalProperties : null,
                    $_changing
                );
            }
            $this->checkArray($value, $schema, $path, $i, $_changing);
        }
        ############ verificar!
        else if (isset($schema->properties) && is_object($value)) {
            $this->checkObj(
                $value,
                $schema->properties,
                $path,
                isset($schema->additionalProperties) ? $schema->additionalProperties : null,
                $_changing
            );
        }
        // verify a regex pattern
        if (isset($schema->pattern) && is_string($value) && !preg_match('/'.$schema->pattern.'/', $value)) {
            $this->adderror($path,"does not match the regex pattern " . $schema->pattern);
        }
        // verify maxLength, minLength, maximum and minimum values
        if (isset($schema->maxLength) && is_string($value) && (strlen($value) > $schema->maxLength)) {
            $this->adderror($path,"must be at most " . $schema->maxLength . " characters long");
        }
        if (isset($schema->minLength) && is_string($value) && strlen($value) < $schema->minLength) {
            $this->adderror($path,"must be at least " . $schema->minLength . " characters long");
        }

        if (
            isset($schema->minimum) &&
            gettype($value) == gettype($schema->minimum) &&
            $value < $schema->minimum
        ) {
            $this->adderror($path,"must have a minimum value of " . $schema->minimum);
        }
        if (isset($schema->maximum) && gettype($value) == gettype($schema->maximum) && $value > $schema->maximum) {
            $this->adderror($path,"must have a maximum value of " . $schema->maximum);
        }
        // verify enum values
        if (isset($schema->enum)) {
            $found = false;
            foreach ($schema->enum as $possibleValue) {
                if ($possibleValue == $value) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->adderror($path,"does not have a value in the enumeration " . implode(', ', $schema->enum));
            }
        }
        if (
            isset($schema->maxDecimal) &&
            (  ($value * pow(10, $schema->maxDecimal)) != (int)($value * pow(10, $schema->maxDecimal))  )
        ) {
            $this->adderror($path,"may only have " . $schema->maxDecimal . " digits of decimal places");
        }
    }

    protected function adderror($path, $message)
    {
        $this->errors[] = array(
            'property'=>$path,
            'message'=>$message
        );
    }

    /**
     * Take Care: Value is being passed by ref to continue validation with proper format.
     * @return array
     */
    protected function checkType($type, &$value, $path)
    {
        if ($type) {
            $wrongType = false;
            if (is_string($type) && $type !== 'any') {
                if ($type == 'null') {
                    if (!is_null($value)) {
                        $wrongType = true;
                    }
                }
                else {
                    if ($type == 'number') {
                        if ($this->checkMode == $this::CHECK_MODE_TYPE_CAST) {
                            $wrongType = !$this->checkTypeCast($type, $value);
                        }
                        else if (!in_array(gettype($value),array('integer','double'))) {
                            $wrongType = true;
                        }
                    } else{
                        if (
                            $this->checkMode == $this::CHECK_MODE_TYPE_CAST
                            && $type == 'integer'
                        ) {
                            $wrongType = !$this->checkTypeCast($type, $value);
                        } else if (
                            $this->checkMode == $this::CHECK_MODE_TYPE_CAST
                            && $type == 'object' && is_array($value)
                        ) {
                            $wrongType = false;
                        } else if ($type !== gettype($value)) {
                            $wrongType = true;
                        }
                    }
                }
            }
            if ($wrongType) {
                return array(
                    array(
                        'property'=>$path,
                        'message'=>gettype($value)." value found, but a ".$type." is required"
                    )
                );
            }
            // Union Types  :: for now, just return the message for the last expected type!!
            if (is_array($type)) {
                $validatedOneType = false;
                $errors = array();
                foreach ($type as $tp) {
                    $error = $this->checkType($tp, $value, $path);
                    if (!count($error)) {
                        $validatedOneType = true;
                        break;
                    } else {
                        $errors[] = $error;
                        $errors = $error;
                    }
                }
                if (!$validatedOneType) {
                    return $errors;
                }
            } else if (is_object($type)) {
                $this->checkProp($value, $type, $path);
            }
        }
        return array();
    }

    /**
     * Take Care: Value is being passed by ref to continue validation with proper format.
     */
    protected function checkTypeCast($type, &$value)
    {
        switch ($type) {
            case 'integer':
                $castValue = (integer)$value;
                break;
            case 'number':
                $castValue = (double)$value;
                break;
            default:
                trigger_error('this method should only be called for the above supported types.');
                break;
        }
        if ((string)$value == (string)$castValue ) {
            $res = true;
            $value = $castValue;
        } else {
            $res = false;
        }
        return $res;
    }

    protected function checkObj($instance, $objTypeDef, $path, $additionalProp, $_changing)
    {
        if ($objTypeDef instanceOf \stdClass) {
            if (! (($instance instanceOf \stdClass) || is_array($instance)) ) {
                $this->errors[] = array(
                    'property'=>$path,
                    'message'=>"an object is required"
                );
            }
            foreach ($objTypeDef as $i=>$value) {
                $value =
                    array_key_exists($i, $instance) ?
                        (is_array($instance) ? $instance[$i] : $instance->$i) :
                        new Undefined();
                $propDef = $objTypeDef->$i;
                $this->checkProp($value, $propDef, $path, $i, $_changing);
            }
        }
        // additional properties and requires
        foreach ($instance as $i=>$value) {
            // verify additional properties, when its not allowed
            if (!isset($objTypeDef->$i) && ($additionalProp === false) && $i !== '$schema' ) {
                $this->errors[] = array(
                    'property'=>$path,
                    'message'=>"The property " . $i . " is not defined in the objTypeDef and the objTypeDef does not allow additional properties"
                );
            }
            // verify requires
            if ($objTypeDef && isset($objTypeDef->$i) && isset($objTypeDef->$i->requires)) {
                $requires = $objTypeDef->$i->requires;
                if (!array_key_exists($requires, $instance)) {
                    $this->errors[] = array(
                        'property'=>$path,
                        'message'=>"the presence of the property " . $i . " requires that " . $requires . " also be present"
                    );
                }
            }
            $value = is_array($instance) ? $instance[$i] : $instance->$i;

            // To verify additional properties types.
            if ($objTypeDef && is_object($objTypeDef) && !isset($objTypeDef->$i)) {
                $this->checkProp($value, $additionalProp, $path, $i);
            }
            // Verify inner schema definitions
            $schemaPropName = '$schema';
            if (!$_changing && $value && isset($value->$schemaPropName)) {
                $this->errors = array_merge(
                    $this->errors,
                    checkProp($value, $value->$schemaPropname, $path, $i)
                );
            }
        }
        return $this->errors;
    }
}
