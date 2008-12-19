$(document).ready(function(){
  $("#bt-validate-js").click(validateJs);
  $("#bt-validate-php").click(validatePhp);
  $("#bt-validate-php-type-cast-mode").click(validatePhpTypeCastMode);
});


function validatePhpTypeCastMode() {
  validatePhp(true);
}

function validatePhp(typeCastMode) {
  if(typeCastMode == true) {
    typeCastMode = true;
  }
  else {
    typeCastMode = false;
  }
  
  $('#resultados').html('. . . w o r k i n g . . . ');
  schema = $('#schema').val();
  json    = $('#json').val();
  
  $.getJSON(
    "validate.php",
    {"schema":schema,"json":json,"typeCastMode":typeCastMode},
    phpCallback
  );
}

function phpCallback(json) {
  showResponse(json);
}

function validateJs() {
  $('#resultados').html('. . . w o r k i n g . . . ');
  jsons = getJsons();
  //alert(print_r(jsons,true));
  if(jsons.schema) {
    validateResponse = JSONSchema.validate(jsons.json,jsons.schema);
  }
  else {
    validateResponse = JSONSchema.validate(jsons.json);
  }
  showResponse(validateResponse);
}

function getJsons() {
  schema = $('#schema').val();
  json    = $('#json').val();
  json = eval( '(' + json + ')' );
  if(schema) {
    schema = eval( '(' + schema + ')' );
  }
  return {"json":json,"schema":schema};
}

function showResponse(validateResponse) {
  //alert(print_r(validateResponse,true));
  res = '<b>'+'JSON: '+(validateResponse.valid?' VALID':'INVALID')+'</B><BR/>';
  $.each(validateResponse.errors,function(i,item) {
    //alert(print_r(item,true));
    res += '<b>' + item.property + '</b> :: ' + item.message + '<br/><br/>';
    res += '<span class=comment>'+(i+":"+print_r(item,true))+"</span><BR/><br/>";
  });
  $('#resultados').html(res);
}