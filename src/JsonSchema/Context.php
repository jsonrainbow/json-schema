<?php

class Context {

	function __construct($schema, $options, $propertyPath, $base, $schemas) {
		$this->schema = $schema;
	  $this->options = $options;
	  $this->propertyPath = $propertyPath;
	  $this->base = $base;
	  $this->schemas = $schemas;
	}

	function resolve($target) {
		http_build_url($this->base, $target);
	}

	function makeChild($schema, $propertyName=null){
  	$propertyPath = ($propertyName===null) ? $this->propertyPath : $this->propertyPath . makeSuffix($propertyName);
  	$base = http_build_url($this->base, $schema->id || '');
  	$ctx = new SchemaContext($schema, $this->options, $propertyPath, $base, $this->schemas); // TODO Object.create(this.schemas)
	  if($schema->id && !$ctx->schemas[$base]){
	    $ctx->schemas[$base] = $schema;
	  }
	  return $ctx;
	}
}