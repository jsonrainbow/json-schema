<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Uri;

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

    function testResolve() {
        $retriever = new UriRetriever();

        $this->assertEquals('http://me.com/base/uri/foo.json'   , $retriever->resolve('foo.json', 'http://me.com/base/uri/dir.json'));
        $this->assertEquals('https://mee.com/base/uri/foo.json' , $retriever->resolve('foo.json', 'https://mee.com/base/uri/dir.json'));
        $this->assertEquals('http://you.net/other-dir/foo.json' , $retriever->resolve('http://you.net/other-dir/foo.json', 'http://mee.com/base/uri/dir.json'));
        $this->assertEquals('https://you.net/other-dir/foo.json', $retriever->resolve('https://you.net/other-dir/foo.json', 'http:///base/uri/dir.json'));
        $this->assertEquals('https://you.net/other-dir/foo.json', $retriever->resolve('https://you.net/other-dir/foo.json', 'https:///base/uri/dir.json'));
        $this->assertEquals('file:///other-dir/foo.json'        , $retriever->resolve('file:///other-dir/foo.json', 'file:///base/uri/dir.json'));

        // question: query and fragmet are dropped. Is this the desired behaviour?
        $this->assertEquals('http://me.com/base/uri/foo.json'   , $retriever->resolve('foo.json?query#fragment', 'http://me.com/base/uri/dir.json'));

    }

}
