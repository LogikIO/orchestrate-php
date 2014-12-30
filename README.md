Orchestrate.io PHP Client
======

This client follows very closely the Orchestrate API and naming conventions, so your best friend is always the (great) Orchestrate API Reference: https://orchestrate.io/docs/apiref

- Uses [Guzzle 5](http://guzzlephp.org/) as HTTP client.
- PHP should be 5.4 or higher.
- JSON is parsed as, and expected to be, associative array.
- You may find it a very user-friendly client.


## Instalation

Use [Composer](http://getcomposer.org):

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of the client:

```bash
composer require andrefelipe/orchestrate-php
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```


## Instantiation
```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
// if you don't provide any parameters it will 
// get the API key from an environment variable 'ORCHESTRATE_API_KEY'
// use the default host 'https://api.orchestrate.io'
// and the default API version 'v0'

// you can also provide the values, in order: key, host, version
$application = new Application(
    $apiKey,
    'https://api.aws-eu-west-1.orchestrate.io/',
    'v0'
);

// check the success with Ping
$application->ping(); // returns boolean
```

## Getting Started
We define our classes following the same convention as Orchestrate, so we have:

1- **Application** — which holds the Guzzle client, and provides a client-like API interface to Orchestrate.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
$object = $application->get('collection_name', 'key'); // returns a KeyValue object
$object = $application->put('collection_name', 'key', ['title' => 'My Title']);
$object = $application->delete('collection_name', 'key');
// you can name the var as '$client' to feel more like it
```

2- **Collection** — which holds a collection name and provides the same client-like API, but with one level-deeper.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
$collection = $application->collection('collection_name');
$object = $collection->get('key');
$object = $collection->put('key', ['title' => 'My Title']);
$object = $collection->delete('key');
```

3- **Objects** — the actual `KeyValue` and `Search` objects, which provides a object-like API, the results and response status.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
$object = new KeyValue($application, 'collection_name', 'key'); // no API calls yet
// you can now change the object as you like, then do the requests later
$object->get();
$object->put(['title' => 'My Title']);
$object->delete();
```

Please note that the result of all operations, in any approach, are exact the same, they all return **Objects**. And ***Objects* holds the results as well as the response status.**

Example:

```php
$application = new Application();
$object = $application->get('collection_name', 'key');

if ($object->isSuccess()) {
    print_r($object->getValue());
    // Array
    // (
    //     [title] => My Title
    // )
} else {
    // in case if was an error, it would return results like these

    echo $object->getStatus(); // items_not_found
    // — the Orchestrate Error code
    
    echo $item->getStatusCode();  // 404
    // — the HTTP response status code

    echo $item->getStatusMessage(); // The requested items could not be found.
    // — the status message, in case of error, the Orchestrate message is used
    // intead of the default HTTP status texts
    
    print_r($item->getBody());
    // Array
    // (
    //     [message] => The requested items could not be found.
    //     [details] => Array
    //         (
    //             [items] => Array
    //                 (
    //                     [0] => Array
    //                         (
    //                             [collection] => collection_name
    //                             [key] => key
    //                         )
    //                 )
    //         )
    //     [code] => items_not_found
    // )
    // — the full body of the response, in this case the Orchestrate error

}

```

All objects implements [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php), so you can access the results directly, like a real Array:

```php

// for KeyValue objects, the resulting value is acessed like:

$object = $application->get('collection_name', 'key');

if (count($object)) // 1 in this case
{
    echo $object['title']; // My Title
    
    foreach ($object as $key => $value)
    {
        echo $key; // title
        echo $value; // My Title
    }
}

// as intended you can change the value, then put back to Orchestrate

$object['file_url'] = 'http://myfile.jpg';
$object->put();

if ($object->isSuccess()) {
    echo $object->getRef(); // cbb48f9464612f20 (the new ref)
    echo $object->getStatus();  // ok
    echo $object->getStatusCode();  // 200
}

// if you don't want to use the internal Array directly, you can always use:
$values = $object->getValue();
// it will return the internal Array that is being accessed

```

To sum how this client works:
- All requests are actually triggered from the **Objects**.
- They prepare the request options and send to the **Application** HTTP client.
- Then the **Objects** store HTTP response and process the results, according to each use case.

Let's go:


## Orchestrate API


### Key/Value
```php
$object = $application->get('collection', 'key');

if ($object->isSuccess()) {
    
    $object->getKey(); // string
    $object->getRef(); // string
    $object->getValue(); // array
    
    $object['my_property']; // direct array access to the Value
    foreach ($object as $key => $value) {} // iteratable
    $object['my_property'] = 'new value'; // set
    unset($object['my_property']); // unset
    
    $object->put(); // put the current value, if has changed, otherwise return
    $object->put(null); // same as above
    $object->put(['title' => 'new title']); // put a new value

    $object->isDirty(); // (boolean) if the internal Array has changed
    $object->isSuccess(); // (boolean) if the last request was sucessful
    $object->isError(); // (boolean) if the last request was not sucessful
    
    $object->getResponse(); // GuzzleHttp\Message\Response
    $object->getStatus(); // ok, created, items_not_found, etc
    $object->getStatusCode(); // (int) the HTTP response status code
    $object->getStatusMessage(); // Orchestrate response message, or HTTP Reason-Phrase

    $object->getRequestId(); // Orchestrate request id, X-ORCHESTRATE-REQ-ID
    $object->getRequestDate(); // the HTTP Date header
    $object->getRequestUrl(); // the effective URL that resulted in this response
    
    $object->getBody(); // the HTTP response body
    // if success is the same as $object->getValue()
    // if error you can read the response error body

}
```

#### Key/Value Get

```php
$object = $application->get('collection', 'key');
// or
$object = $collection->get('key');
// or
$object = new KeyValue($application, 'collection', 'key');
$object->get();
```

#### Key/Value Put (create/update by key)

```php
$object = $application->put('collection', 'key', ['title' => 'New Title']);
// or
$object = $collection->get('key', ['title' => 'New Title']);
// or
$object = new KeyValue($application, 'collection', 'key');
$object['title'] = 'New title';
$object->put(); // puts the whole current value, only with the title changed
$object->put(['title' => 'New Title']); // puts an entire new value
```


## Docs

Please refer to the source code for now, while a proper documentation is made.



## Useful Notes

Here are some useful notes to consider when using the Orchestrate service:
- Avoid using slashes (/) in the key name, some problems will arise when querying them;
- If applicable, remember you can use a composite key like `{deviceID}_{sensorID}_{timestamp}` for your KeyValue keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list