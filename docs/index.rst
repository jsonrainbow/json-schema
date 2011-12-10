.. JsonSchema documentation master file, created by
   sphinx-quickstart on Sat Dec 10 15:34:44 2011.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to JsonSchema's documentation!
======================================

Contents:

.. toctree::
   :maxdepth: 2


Installation
------------

.. code-block:: console
   
   git clone --recursive https://github.com/justinrainbow/json-schema.git


Usage
-----

.. code-block:: php
   
   <?php
   $validator = new JsonSchema\Validator();
   $result = $validator->validate(json_decode($json), json_decode($schema));

   if ($result->valid) {
       echo "The supplied JSON validates against the schema.\n";
   } else {
       echo "JSON does not validate. Violations:\n";
       foreach ($result->errors as $error) {
           echo "[{$error['property']}] {$error['message']}\n";
       }
   }


Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`

