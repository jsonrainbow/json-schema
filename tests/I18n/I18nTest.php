<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\I18n;

use JsonSchema\Validator;

class I18nTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getInvalidTests
     */
    public function testTranslation($schema, $sample, $lang, $msg)
    {
        \Locale::setDefault($lang);
        $validator = new Validator();
        $validator->check($sample, $schema);
        $errors = $validator->getErrors();
        $this->assertRegexp("/$msg/", $errors[0]['message']);
    }

    /**
     * Returns a set of test scenarios for each translation test
     * [0] schema
     * [1] invalid sample
     * [2] language
     * [3] fragment of message in the language
     */
    public function getInvalidTests()
    {
        return [
            /* minItems */
            [(object)["id"=>"value", "type"=>"array", "minItems"=>2], [1], 'en_US', 'There must be a minimum'],
            [(object)["id"=>"value", "type"=>"array", "minItems"=>2], [1], 'pt_BR', 'É preciso haver um mínimo'],
            /* maxItems */
            [(object)["id"=>"value", "type"=>"array", "maxItems"=>1], [1,2], 'en_US', 'There must be a maximum'],
            [(object)["id"=>"value", "type"=>"array", "maxItems"=>1], [1,2], 'pt_BR', 'É preciso haver um máximo'],
            /* uniqueItems */
            [(object)["id"=>"value", "type"=>"array", "uniqueItems"=>true], [1,1], 'en_US', 'There are no duplicates allowed'],
            [(object)["id"=>"value", "type"=>"array", "uniqueItems"=>true], [1,1], 'pt_BR', 'Não são permitidos elementos duplicados'],
            /* minimum */
            [(object)["id"=>"value", "type"=>"number", "minimum"=>10], 1, 'en_US', 'Must have a minimum'],
            [(object)["id"=>"value", "type"=>"number", "minimum"=>10], 1, 'pt_BR', 'Precisa de um valor mínimo'],
            /* maximum */
            [(object)["id"=>"value", "type"=>"number", "maximum"=>10], 11, 'en_US', 'Must have a maximum'],
            [(object)["id"=>"value", "type"=>"number", "maximum"=>10], 11, 'pt_BR', 'Precisa de um valor máximo'],
            /* minLength */
            [(object)["id"=>"value", "type"=>"string", "minLength"=>2], 'a', 'en_US', 'Must be at least'],
            [(object)["id"=>"value", "type"=>"string", "minLength"=>2], 'a', 'pt_BR', 'Precisa conter no mínimo'],
            /* maxLength */
            [(object)["id"=>"value", "type"=>"string", "maxLength"=>2], 'abc', 'en_US', 'Must be at most'],
            [(object)["id"=>"value", "type"=>"string", "maxLength"=>2], 'abc', 'pt_BR', 'Precisa conter no máximo'],
            /* pattern */
            [(object)["id"=>"value", "type"=>"string", "pattern"=>"^abc$"], 'abcd', 'en_US', 'Does not match'],
            [(object)["id"=>"value", "type"=>"string", "pattern"=>"^abc$"], 'abcd', 'pt_BR', 'Não corresponde ao padrão'],
        ];
    }
}
