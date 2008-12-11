/** 
 * JSONSchema Validator - Validates JavaScript objects using JSON Schemas 
 *	(http://www.json.com/json-schema-proposal/)
 *
 * Copyright (c) 2007 Kris Zyp SitePen (www.sitepen.com)
 * Licensed under the MIT (MIT-LICENSE.txt) licence.
To use the validator call JSONSchema.validate with an instance object and an optional schema object.
If a schema is provided, it will be used to validate. If the instance object refers to a schema (self-validating), 
that schema will be used to validate and the schema parameter is not necessary (if both exist, 
both validations will occur). 
The validate method will return an array of validation errors. If there are no errors, then an 
empty list will be returned. A validation error will have two properties: 
"property" which indicates which property had the error
"message" which indicates what the error was
 */

JSONSchema = {
	validate : function(/*Any*/instance,/*Object*/schema) {
		// Summary:
		//  	To use the validator call JSONSchema.validate with an instance object and an optional schema object.
		// 		If a schema is provided, it will be used to validate. If the instance object refers to a schema (self-validating), 
		// 		that schema will be used to validate and the schema parameter is not necessary (if both exist, 
		// 		both validations will occur). 
		// 		The validate method will return an object with two properties:
		// 			valid: A boolean indicating if the instance is valid by the schema
		// 			errors: An array of validation errors. If there are no errors, then an 
		// 					empty list will be returned. A validation error will have two properties: 
		// 						property: which indicates which property had the error
		// 						message: which indicates what the error was
		//
		return this._validate(instance,schema,false);
	},
	checkPropertyChange : function(/*Any*/value,/*Object*/schema) {
		// Summary:
		// 		The checkPropertyChange method will check to see if an value can legally be in property with the given schema
		// 		This is slightly different than the validate method in that it will fail if the schema is readonly and it will
		// 		not check for self-validation, it is assumed that the passed in value is already internally valid.  
		// 		The checkPropertyChange method will return the same object type as validate, see JSONSchema.validate for 
		// 		information.
		//
		return this._validate(value,schema,true);
	},
	_validate : function(/*Any*/instance,/*Object*/schema,/*Boolean*/ _changing) {
		
		
		var errors2 = [];
		// validate a value against a property definition
		
		function checkProp(value, schema, path,i) {
			if (typeof schema != 'object') {
				return;
			}			
			path += path ? typeof i == 'number' ? '[' + i + ']' : typeof i == 'undefined' ? '' : '.' + i : i;
			function addError(message) {
				errors2.push({property:path,message:message});
			}
			if (_changing && schema.readonly)
				addError("is a readonly field, it can not be changed");
/*
if (schema instanceof Array) {
	if (!(value instanceof Array)) {
		return [{property:path,message:"An array tuple is required"}];
	}
	for (i =0; i < schema.length; i++) {
		errors2.concat(checkProp(value[i],schema[i],path,i));
	}
	return errors2;
}
*/
			if (schema['extends']) // if it extends another schema, it must pass that schema as well
				checkProp(value,schema['extends'],path,i);
			// validate a value against a type definition
			function checkType(type,value) {
				if (type) {
					if (typeof type == 'string' && type != 'any' 
							&& (type == 'null' ? value !== null : typeof value != type) 
							&& !(value instanceof Array && type == 'array')
							&& !(type == 'integer' && !(value%1)))
						return [{property:path,message:(typeof value) + " value found, but a " + type + " is required"}];
					if (type instanceof Array) {
						var unionErrors=[];
						for (var j = 0; j < type.length; j++) // a union type 
							if (!(unionErrors=checkType(type[j],value)).length)
								break;
						if (unionErrors.length)
							return unionErrors;
					}
					else if (typeof type == 'object') {
						checkProp(value,type,path);
					}
				}
				return [];
			}
			
			//if (value !== null) {
				if (value === undefined) {
					if (!schema.optional)  
						addError("is missing and it is not optional");
				}
				else {
					errors2 = errors2.concat(checkType(schema.type,value));
					if (schema.disallow && !checkType(schema.disallow,value).length)
						addError(" disallowed value was matched");
					if (value instanceof Array) {
						if (schema.items) {
						  if(schema.items instanceof Array) {
						    for (var k = 0,l=value.length; k < l; k++) {
							      if(k < schema.items.length) {
								errors2.concat(checkProp(value[k],schema.items[k],path,k));
						      }
						      else {
						        if(schema.additionalProperties !== undefined) {
						          if(schema.additionalProperties === false) {
						            addError("The item " + i + "[" + k + "] is not defined in the objTypeDef and the objTypeDef does not allow additional properties");
						          }
						          else {
						            errors2.concat(checkProp(value[k],schema.additionalProperties,path,k));
						          }
						        }
						      }
							}
							if(value.length < schema.items.length) {
							  for (var k = value.length; k < schema.items.length; k++) {
							    errors2.concat(checkProp(undefined,schema.items[k],path,k));
							  }
							}
						  }
						  else {
							for (var i =0,l=value.length; i < l; i++) {
								errors2.concat(checkProp(value[i],schema.items,path,i));
							}	
						  }						
						}
						if (schema.minItems && value.length < schema.minItems) {
							addError("There must be a minimum of " + schema.minItems + " in the array");
						}
						if (schema.maxItems && value.length > schema.maxItems) {
							addError("There must be a maximum of " + schema.maxItems + " in the array");
						}
					}
					else if (schema.properties && typeof value == 'object') {
						errors2.concat(checkObj(value,schema.properties,path,schema.additionalProperties));
					}
					if (schema.pattern && typeof value == 'string' && !value.match(schema.pattern))
						addError("does not match the regex pattern " + schema.pattern);
					if (schema.maxLength && typeof value == 'string' && (value.length > schema.maxLength))
						addError("may only be " + schema.maxLength + " characters long");
					if (schema.minLength && typeof value == 'string' && (value.length < schema.minLength))
						addError("must be at least " + schema.minLength + " characters long");
					if (typeof schema.minimum !== undefined && typeof value == typeof schema.minimum && schema.minimum > value)
						addError("must have a minimum value of " + schema.minimum);
					if (typeof schema.maximum !== undefined && typeof value == typeof schema.maximum && schema.maximum < value)
						addError("must have a maximum value of " + schema.maximum);
					if (schema['enum']) {
						var enumer = schema['enum'];
						var l = enumer.length;
						var found;
						for (var j = 0; j < l; j++)
							if (enumer[j]===value) {
								found=1;
								break;
							}
						if (!found) {
							addError("does not have a value in the enumeration " + enumer.join(", "));
						}
					}
					if (typeof schema.maxDecimal == 'number' && (value * Math.pow(10,schema.maxDecimal))%1) {
					  addError("may only have " + schema.maxDecimal + " digits of decimal places");
					}
				}
			//}
		
		}
		// validate an object against a schema
		function checkObj(instance,objTypeDef,path,additionalProp) {
			if (typeof objTypeDef =='object') {
				if (typeof instance != 'object' || instance instanceof Array)
					errors2.push({property:path,message:"an object is required"});
				
				for (var i in objTypeDef) 
					if (objTypeDef.hasOwnProperty(i)) {
						var value = instance[i];
						var propDef = objTypeDef[i];
						checkProp(value,propDef,path,i);
					}
			}
			for (var i in instance) {
				if (instance.hasOwnProperty(i) && objTypeDef && !objTypeDef[i] && additionalProp===false)
					errors2.push({property:path,message:(typeof value) + "The property " + i + " is not defined in the objTypeDef and the objTypeDef does not allow additional properties"});
				var requires = objTypeDef && objTypeDef[i] && objTypeDef[i].requires;
				if (requires && !(requires in instance))
					errors2.push({property:path,message:"the presence of the property " + i + " requires that " + requires + " also be present"});
				value = instance[i];
				if (objTypeDef && typeof objTypeDef == 'object' && !(i in objTypeDef))
					checkProp(value,additionalProp,path,i); 
//				if (!_changing && value && value.type)
	//				errors2 = errors2.concat(checkObj(value,value.type,path + '.' + i));
				if (!_changing && value && value.$schema)
					errors2 = errors2.concat(checkProp(value,value.$schema,path,i));
			}
			return errors2;
		}
		if (schema)
			checkProp(instance,schema,'','')
		if (!_changing && instance.$schema)
			checkProp(instance,instance.$schema,'','');
		return {valid:!errors2.length,errors:errors2};
	}
	/* will add this later
	newFromSchema : function() {
	}
*/
}
