<?php
use Sami\Sami;
//use Sami\Version\GitVersionCollection;
//$versions = GitVersionCollection::create('lib')
//    ->addFromTags('v5.0')
//    ->add('master', 'master branch')
//;
//return new Sami('lib', [
//    'title' => 'json-schema validator API',
//    'versions' => $versions,
//    'build_dir' => 'docs/%version%',
//    'cache_dir' => 'docs/cache/%version%',
//]);
// for local dev
return new Sami('src', [
	'title' => 'json-schema validator API',
	'build_dir' => 'docs',
	'cache_dir' => 'docs/cache',
	'theme' => "default"
]);