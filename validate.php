<?php
require_once('../../plugins/JsSchema/lib/JsSchema.php');
Dbg::$quietMode = true;

if($_REQUEST['schema']) {
  $schema = json_decode($_REQUEST['schema']);
  if(!$schema) {
    trigger_error('Could not parse the SCHEMA object.',E_USER_ERROR);
  }
}
else {
  $schema = null;
}

$json = json_decode($_REQUEST['json']);
if(!$json) {
  trigger_error('Could not parse the JSON object.',E_USER_ERROR);
}

$result = JsSchema::validate(
  $json,
  $schema
);
header('Content-type: application/x-json');
echo json_encode($result);
?>