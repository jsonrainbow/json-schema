<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema\Tests\Constraints;

class RequiredPropertyTest extends BaseTestCase
{
    public function getInvalidTests()
    {
        return array(
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number","required":true}
                  }
                }'
            ),
            array(
                '{}',
                '{
                    "type": "object",
                    "properties": {
                        "number": {"type": "number"}
                    },
                    "required": ["number"]
                }'
            ),
            array(
                '{
                    "foo": {}
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {
                            "type": "object",
                            "properties": {
                                "bar": {"type": "number"}
                            },
                            "required": ["bar"]
                        }
                    }
                }'
            ),
            array(
                '{
                    "bar": 1.4
                 }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string", "required": true},
                        "bar": {"type": "number"}
                    },
                    "required": ["bar"]
                }'
            ),
            array(
                '{}',
                '{
                    "required": ["foo"]
                }'
            ),
            array(
                '{
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": { "required": true }
                    }
                }'
            ),
            array(
                '{
                  "string":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "string":{"type":"string", "required": true}
                  }
                }'
            ),
            array(
                '{
                  "number":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "number":{"type":"number", "required": true}
                  }
                }'
            ),
            array(
                '{
                  "integer":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "integer":{"type":"integer", "required": true}
                  }
                }'
            ),
            array(
                '{
                  "boolean":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "boolean":{"type":"boolean", "required": true}
                  }
                }'
            ),
            array(
                '{
                  "array":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "array":{"type":"array", "required": true}
                  }
                }'
            ),
            array(
                '{
                  "null":{}
                }',
                '{
                  "type":"object",
                  "properties": {
                    "null":{"type":"null", "required": true}
                  }
                }'
            ),
            array(
                '{
                  "foo": {"baz": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number"}
                      },
                      "required": ["bar"]
                    }
                  }
                }'
            ),
            array(
                '{
                  "foo": {"baz": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number", "required": true}
                      }
                    }
                  }
                }'
            ),
        );
    }

    public function getValidTests()
    {
        return array(
            array(
                '{
                  "number": 1.4
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number","required":true}
                  }
                }'
            ),
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number"}
                  }
                }'
            ),
            array(
                '{}',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"number","required":false}
                  }
                }'
            ),
            array(
                '{
                  "number": 0
                }',
                '{
                  "type":"object",
                  "properties":{
                    "number":{"type":"integer","required":true}
                  }
                }'
            ),
            array(
                '{
                  "is_active": false
                }',
                '{
                  "type":"object",
                  "properties":{
                    "is_active":{"type":"boolean","required":true}
                  }
                }'
            ),
            array(
                '{
                  "status": null
                }',
                '{
                  "type":"object",
                  "properties":{
                    "status":{"type":"null","required":true}
                  }
                }'
            ),
            array(
                '{
                  "users": []
                }',
                '{
                  "type":"object",
                  "properties":{
                    "users":{"type":"array","required":true}
                  }
                }'
            ),
            array(
                '{
                    "foo": "foo",
                    "bar": 1.4
                 }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {"type": "string", "required": true},
                        "bar": {"type": "number"}
                    },
                    "required": ["bar"]
                }'
            ),
            array(
                '{
                    "foo": {"bar": 1.5}
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": {
                            "type": "object",
                            "properties": {
                                "bar": {"type": "number"}
                            },
                            "required": ["bar"]
                        }
                    },
                    "required": ["foo"]
                }'
            ),
            array(
                '{
                    "foo": {}
                }',
                '{
                    "type": "object",
                    "properties": {
                        "foo": { "required": true }
                    }
                }'
            ),
            array(
                '{
                  "boo": {"bar": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number"}
                      },
                      "required": ["bar"]
                    }
                  }
                }'
            ),
            array(
                '{
                  "boo": {"bar": 1.5}
                }',
                '{
                  "type": "object",
                  "properties": {
                    "foo": {
                      "type": "object",
                      "properties": {
                        "bar": {"type": "number", "required": true}
                      }
                    }
                  }
                }'
            ),
        );
    }
}
