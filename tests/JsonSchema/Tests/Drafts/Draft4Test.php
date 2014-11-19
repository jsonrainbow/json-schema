<?php

namespace JsonSchema\Tests\Drafts;

class Draft4Test extends BaseDraftTestCase
{
    protected function getFilePaths()
    {
        return array(
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4'),
            realpath(__DIR__ . $this->relativeTestsRoot . '/draft4/optional')
        );
    }

    protected function getSkippedTests()
    {
        return array(
            // Not Yet Implemented
            'definitions.json',
            // Partially Implemented
            'ref.json',
            'refRemote.json',
            // Optional
            'bignum.json',
            'zeroTerminatedFloats.json'
        );
    }
    
    public function testInnerDefinitions(){
        $schema = <<<JSN
{
    "type": "object",
    "additionalProperties":false,
    "properties": {
        "person": { "\$ref": "#/definitions/persondef" },
        "person-age": { "\$ref": "#/definitions/persondef/properties/age" },
        "slash": { "\$ref": "#/definitions/persondef/properties/~1slash" },
        "tilde": { "\$ref": "#/definitions/persondef/properties/~0tilde" }
    },
    "definitions": {
        "persondef": {
            "type": "object",
            "additionalProperties":false,
            "properties": {
                "name": {
                    "type": "string"
                },
                "age": {
                    "type" : "integer"
                },
                "/slash": {
                    "type" : "integer"
                },
                "~tilde": {
                    "type" : "integer"
                }
            }
        }
    }
}
JSN;
        $schemaObj = json_decode($schema);
        $resolver = new \JsonSchema\RefResolver();
        $resolver->resolve($schemaObj);
        
        $schema = json_encode($schemaObj);
        
        $this->testValidCases('{"person": {"name" : "John Doe", "age" : 30} }', $schema);
        $this->testInvalidCases('{"person": {"name" : "John Doe", "age" : "wrong"} }', $schema);
        $this->testValidCases('{"person-age": 30 }', $schema);
        $this->testInvalidCases('{"person-age": "wrong" }', $schema);
        $this->testValidCases('{"slash": 30 }', $schema);
        $this->testInvalidCases('{"slash": "wrong" }', $schema);
        $this->testValidCases('{"tilde": 30 }', $schema);
        $this->testInvalidCases('{"tilde": "wrong" }', $schema);
    }

}
