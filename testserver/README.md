## testserver

a small node.js based http server providing the json-schema test-suite [remote schemas](../tests/suite/remotes) to the [json-schema SuiteTest](../#tests) .

## Install

Install [node.js](http://nodejs.org/).
Install the package manager for node: [npm](https://npmjs.org/)  (If not already included in node.js)

Install required node.js packages via npm

	npm install

## Start server

	cd testserver
	node server.js

	http://localhost:1234   serving directory /<path-to-json-schema>/tests/suite/remotes

Try the testserver:
Point your webbrowser to [http://localhost:1234/integer.json](http://localhost:1234/integer.json)
This should download a small file `Content-Type: application/schema+json` containing

```json
{
    "type": "integer"
}
```

ok, the testserver is running.
Now you can run the [json-schema tests](../#tests).
