<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Uri;

use JsonSchema\Validator;

class UriRetrieverTest extends \PHPUnit_Framework_TestCase
{
    protected $validator;

    protected function setUp()
    {
        $this->validator = new Validator();
    }
    
    private function getRetrieverMock($returnSchema, $returnMediaType = Validator::SCHEMA_MEDIA_TYPE)
    {
        $retriever = $this->getMock('JsonSchema\Uri\UriRetriever', array('retrieve'));
        
        $retriever->expects($this->at(0))
                  ->method('retrieve')
                  ->with($this->equalTo(null), $this->equalTo('http://some.host.at/somewhere/parent'))
                  ->will($this->returnValue($returnSchema));
        
        return $retriever;
    }
    
    /**
     * @dataProvider jsonProvider 
     */
    public function testChildExtendsParent($childSchema, $parentSchema)
    {
        $retrieverMock = $this->getRetrieverMock($parentSchema);
        
        $json = '{"childProp":"infant", "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);
        
        $this->validator->setUriRetriever($retrieverMock);
        $this->validator->check($decodedJson, $decodedJsonSchema);
        $this->assertTrue($this->validator->isValid());
    }
    
    /**
     * @dataProvider jsonProvider 
     */
    public function testResolveRelativeUri($childSchema, $parentSchema)
    {
        self::setParentSchemaExtendsValue($parentSchema, 'grandparent');
        $retrieverMock = $this->getRetrieverMock($parentSchema);
        $json = '{"childProp":"infant", "parentProp":false}';
        $decodedJson = json_decode($json);
        $decodedJsonSchema = json_decode($childSchema);
        
        $this->validator->setUriRetriever($retrieverMock);
        $this->validator->check($decodedJson, $decodedJsonSchema);
        $this->assertTrue($this->validator->isValid());
    }
    
    private static function setParentSchemaExtendsValue(&$parentSchema, $value)
    {
        $parentSchemaDecoded = json_decode($parentSchema, true);
        $parentSchemaDecoded['extends'] = $value;
        $parentSchema = json_encode($parentSchemaDecoded);
    }
    
    public function jsonProvider()
    {
        $childSchema = <<<EOF
{
    "type":"object",    
    "title":"child",
    "extends":"http://some.host.at/somewhere/parent",
    "properties":
    {
        "childProp":
        {
            "type":"string"
        }
    }
}
EOF;
        $parentSchema = <<<EOF
{
    "type":"object",    
    "title":"parent",
    "properties":
    {
        "parentProp":
        {
            "type":"boolean"
        }
    }
}
EOF;
        return array(
            array($childSchema, $parentSchema)
        );
    }
}
