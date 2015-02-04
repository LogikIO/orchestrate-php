Orchestrate.io PHP Client
======

A very straight forward PHP client for [Orchestrate.io](https://orchestrate.io) DBaaS.

- PHP's [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php) built in on every response.
- Orchestrate's error responses are honored.
- Uses [Guzzle 5](http://guzzlephp.org/) as HTTP client.
- PHP must be 5.4 or higher.
- Adheres to PHP-FIG [PSR-2](http://www.php-fig.org/psr/psr-2/) and [PSR-4](http://www.php-fig.org/psr/psr-4/)
- JSON is parsed as, and expected to be, associative array.
- You may find it a very user-friendly client.

This client follows very closely [Orchestrate's](https://orchestrate.io) naming conventions, so you can confidently rely on the Orchestrate API Reference: https://orchestrate.io/docs/apiref

[![Latest Stable Version](https://poser.pugx.org/andrefelipe/orchestrate-php/v/stable.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)
[![License](https://poser.pugx.org/andrefelipe/orchestrate-php/license.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)


## Instalation

Use [Composer](http://getcomposer.org).

Install Composer Globally (Linux / Unix / OSX):

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Run this Composer command to install the latest stable version of the client, in the current folder:

```bash
composer require andrefelipe/orchestrate-php
```

After installing, require Composer's autoloader and you're good to go:

```php
<?php
require 'vendor/autoload.php';
```



## Instantiation
```php
use andrefelipe\Orchestrate\Application;

$application = new Application();
// if you don't provide any parameters it will:
// get the API key from an environment variable 'ORCHESTRATE_API_KEY'
// use the default host 'https://api.orchestrate.io'
// and the default API version 'v0'

// you can also provide the parameters, in order: apiKey, host, version
$application = new Application(
    'your-api-key',
    'https://api.aws-eu-west-1.orchestrate.io/',
    'v0'
);

// check the success with Ping
$application->ping(); // (boolean)
```

## Getting Started
We define our classes following the same convention as Orchestrate, so we have:

1- **Application** — which holds the credentials and HTTP client, and provides a client-like API interface to Orchestrate.

```php
use andrefelipe\Orchestrate\Application;

$application = new Application();

$item = $application->get('collection', 'key'); // returns a KeyValue object
$item = $application->put('collection', 'key', ['title' => 'My Title']);
$item = $application->delete('collection', 'key');
// you can name the $application var as '$client' to feel more like a client
```

2- **Collection** — which holds a collection name and provides the same client-like API, but with one level-deeper.

```php
use andrefelipe\Orchestrate\Application;
use andrefelipe\Orchestrate\Collection;

$application = new Application();

$collection = new Collection('collection');
$item->setApplication($application); // link to the client

$item = $collection->get('key');
$item = $collection->put('key', ['title' => 'My Title']);
$item = $collection->delete('key');
```

3- **Objects** — the actual Orchestrate objects, which provides a object-like API, as well as the results, response status, and pagination methods. They split in two categories:

**Single Objects**, which provides methods to manage a single entity (get/put/delete/etc):
- `KeyValue`, core to Orchestrate and our client, handles key/ref/value;
- `Ref`, a KeyValue subclass, adds the tombstone and reftime properties;
- `Event`, provides a similar API as the KeyValue, for the Event object;
- `SearchResult`, a KeyValue subclass, adds the score and distance properties.

**List of Objects**, which provides the results and pagination methods: 
- `KeyValues`, used for KeyValue List query
- `Refs`, used for Refs List query
- `Graph`, used for Graph Get query
- `Events`, used for Event List query
- `Search`, used for Search query, with support for aggregates

```php
use andrefelipe\Orchestrate\Application;
use andrefelipe\Orchestrate\Objects\KeyValue;

$application = new Application();

$item = new KeyValue('collection', 'key'); // no API calls yet
$item->setApplication($application); // link to the client

$item->get(); // API call to get the current key
$item->get('20c14e8965d6cbb0'); // get a specific ref
$item->put(['title' => 'My Title']); // puts a new value
$item->delete(); // delete the current ref
```

Choosing one approach over the other is a matter of your use case. For one-stop actions you'll find easier to work with the Application or Collection. But on a programatically import. for example. it will be nice to use the objects directly because you can store and manage the data, then later do the API calls.

Remember, the credentials and the HTTP client are only available at the `Application` object, so all objects must reference to it in order to work. You can do so via:
```php
$item = new KeyValue('collection', 'key');
$item->setApplication($application);
// where $application is an Application instance
```


## Responses

The result of all operations, in any approach, are exact the same, they all return **Objects**. And **Objects holds the results as well as the response status.**

Example:

```php
$application = new Application();
$item = $application->get('collection', 'key'); // returns a KeyValue object

if ($item->isSuccess()) {

    print_r($item->getValue());
    // Array
    // (
    //     [title] => My Title
    // )

    print_r($item->toArray());
    // Array
    // (
    //     [kind] => item
    //     [path] => Array
    //         (
    //             [collection] => collection
    //             [key] => key
    //             [ref] => 3eb18d8d034a3530
    //         )
    //     [value] => Array
    //         (
    //             [title] => My Title
    //         )
    // )

} else {
    // in case if was an error, it would return results like these:

    echo $item->getStatus(); // items_not_found
    // — the Orchestrate Error code
    
    echo $item->getStatusCode();  // 404
    // — the HTTP response status code

    echo $item->getStatusMessage(); // The requested items could not be found.
    // — the status message, in case of error, the Orchestrate message is used
    // intead of the default HTTP Reason-Phrases
    
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
    //                             [collection] => collection
    //                             [key] => key
    //                         )
    //                 )
    //         )
    //     [code] => items_not_found
    // )
    // — the full body of the response, in this case, the Orchestrate error

}

```


## Array Access

All objects implements PHP's [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php), so you can access the results directly, like a real Array.

Example:

```php

// considering KeyValue with the value of {"title": "My Title"}

$item = $application->get('collection', 'key');

if (count($item)) { // get the property count 

    if (isset($item['title'])) {
        echo $item['title'];  // My Title
    }
    
    foreach ($item as $key => $value) {
        echo $key; // title
        echo $value; // My Title
    }
}

// as intended you can change the Value, then put back to Orchestrate
$item['file_url'] = 'http://myfile.jpg';
$item->put();

if ($item->isSuccess()) {
    echo $item->getRef(); // cbb48f9464612f20 (the new ref)
    echo $item->getStatus();  // ok
    echo $item->getStatusCode();  // 200
}


// if you don't want to use the internal Value Array directly, you can always get it with:
$value = $item->getValue();

// also all objects provide an additional method, toArray()
// which returns an Array representation of the object
print_r($item->toArray());
// Array
// (
//     [kind] => item
//     [path] => Array
//         (
//             [collection] => collection
//             [key] => key
//             [ref] => cbb48f9464612f20
//             [reftime] => 1400085084739
//             [score] => 1.0
//             [tombstone] => true
//         )
//     [value] => Array
//         (
//             [title] => My Title
//         )
// )


// Of course, it gets interesting on List objects like Search:

$results = $application->search('collection', 'title:"The Title*"');

// you can iterate over the results directly!
foreach ($results as $item) {
    
    // get its values
    $item->getValue(); // the Value
    $item->getScore(); // search score
    $item->getDistance(); // populated if it was a Geo query

    // and manage them
    $item->putRelation('kind', 'toCollection', 'toKey');

    // if relation was created successfuly
    if ($item->isSuccess()) {

        // take the opportunity to post an event
        $values = ['type' => 'relation', 'to' => 'toKey', 'ref' => $item->getRef()];

        $application->postEvent('collection', $item->getKey(), 'log', $values);
    }
}




Let's go:



## Orchestrate API


### Application Ping:
> returns Boolean

```php
if ($application->ping()) {
    // good
}
```


### Collection Delete:
> returns Boolean

```php
if ($application->deleteCollection('collection')) {
    // good
}
```


### Key/Value Get
> returns KeyValue object

```php
$item = $application->get('collection', 'key');
// or
$item = $collection->get('key');
// or
$item = new KeyValue('collection', 'key');
$item->get();

// get the object info
$item->getKey(); // string
$item->getRef(); // string
$item->getValue(); // array of the Value
$item->toArray(); // array representation of the object
$item->getBody(); // array of the unfiltered HTTP response body
```


### Key/Value Put (create/update by key)
> returns KeyValue object

```php
$item = $application->put('collection', 'key', ['title' => 'New Title']);
// or
$item = $collection->put('key', ['title' => 'New Title']);
// or
$item = new KeyValue('collection', 'key');
$item['title'] = 'New Title';
$item->put(); // puts the whole current Value, only with the title changed
$item->put(['title' => 'New Title']); // puts an entire new value
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$item = $application->put('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$item = $collection->put('key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$item = new KeyValue('collection', 'key');
$item->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$item->put(['title' => 'New Title'], true); // uses the current object Ref
```


**Conditional Put If-None-Match**:

Stores the value for the key if no key/value already exists.

```php
$item = $application->put('collection', 'key', ['title' => 'New Title'], false);
// or
$item = $collection->put('key', ['title' => 'New Title'], false);
// or
$item = new KeyValue('collection', 'key');
$item->put(['title' => 'New Title'], false);
```


### Key/Value Patch (partial update - Operations)
> returns KeyValue object

Please refer to the [API Reference](https://orchestrate.io/docs/apiref#keyvalue-patch) for all details about the operations.

```php
// uses the Patch operation builder
use andrefelipe\Orchestrate\Query\PatchBuilder;

$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

$item = $application->patch('collection', 'key', $patch);
// or
$item = $collection->patch('key', $patch);
// or
$item = new KeyValue('collection', 'key');
$item->patch($patch);

// Warning: when patching, the object Value (retrievable with $item->getValue())
// WILL NOT be updated! Orchestrate does not (yet) return the Value body in
// Patch operations, and mocking on our side will be very inconsistent
// and an extra GET would have to issued anyway.

// As a solution, you can fetch the resulting Value, using the
// third parameter 'reload' as:
$item->patch($patch, null, true);

// it will reload the data with $item->get($item->getRef());
// if the patch was successful
```

**Conditional Patch (Operations) If-Match**:

Updates the value for the key if the value for this header matches the current ref value.

```php
$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

$item = $application->patch('collection', 'key', $patch, '20c14e8965d6cbb0');
// or
$item = $collection->patch('key', $patch, '20c14e8965d6cbb0');
// or
$item = new KeyValue('collection', 'key');
$item->patch($patch, '20c14e8965d6cbb0');
$item->patch($patch, true); // uses the current object Ref
$item->patch($patch, true, true); // with the reload as mentioned above
```


### Key/Value Patch (partial update - Merge)
> returns KeyValue object

```php
$item = $application->patchMerge('collection', 'key', ['title' => 'New Title']);
// or
$item = $collection->patchMerge('key', ['title' => 'New Title']);
// or
$item = new KeyValue('collection', 'key');
$item['title'] = 'New Title';
$item->patchMerge(); // merges the current Value
$item->patchMerge(['title' => 'New Title']); // or merge with new value
// also has a 'reload' parameter as mentioned above
```


**Conditional Patch (Merge) If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$item = $application->patchMerge('collection', 'key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$item = $collection->patchMerge('key', ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$item = new KeyValue('collection', 'key');
$item->patchMerge(['title' => 'New Title'], '20c14e8965d6cbb0');
$item->patchMerge(['title' => 'New Title'], true); // uses the current object Ref
// also has a 'reload' parameter as mentioned above
```



### Key/Value Post (create & generate key)
> returns KeyValue object

```php
$item = $application->post('collection', ['title' => 'New Title']);
// or
$item = $collection->post(['title' => 'New Title']);
// or
$item = new KeyValue('collection');
$item['title'] = 'New Title';
$item->post(); // posts the current Value
$item->post(['title' => 'New Title']); // posts a new value
```


### Key/Value Delete
> returns KeyValue object

```php
$item = $application->delete('collection', 'key');
// or
$item = $collection->delete('key');
// or
$item = new KeyValue('collection', 'key');
$item->delete();
$item->delete('20c14e8965d6cbb0'); // delete the specific ref
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
$item = $application->delete('collection', 'key', '20c14e8965d6cbb0');
// or
$item = $collection->delete('key', '20c14e8965d6cbb0');
// or
$item = new KeyValue('collection', 'key');
// first get or set a ref:
// $item->get();
// or $item->setRef('20c14e8965d6cbb0');
$item->delete(true); // delete the current ref
$item->delete('20c14e8965d6cbb0'); // delete a specific ref
```


**Purge**:

The KV object and all of its ref history will be permanently deleted. This operation cannot be undone.

```php
$item = $application->purge('collection', 'key');
// or
$item = $collection->purge('key');
// or
$item = new KeyValue('collection', 'key');
$item->purge();
```



### Key/Value List:
> returns KeyValues object, with results as KeyValue objects

```php
$list = $application->listCollection('collection');
// or
$collection = new Collection('collection');
$list = $collection->listCollection();
// or
$list = new KeyValues('collection'); // note the plural
$list->listCollection();


// get array of the results (KeyValue objects)
$list->getResults();

// or go ahead and iterate over the results directly
foreach ($list as $item) {
    
    $item->getValue();
    // items are KeyValue objects
}

// pagination
$list->getNextUrl(); // string
$list->getPrevUrl(); // string
$list->getCount(); // count of the current set of results
$list->getTotalCount(); // count of the total results available
$list->next(); // loads next set of results
$list->prev(); // loads previous set of results
```



### Refs Get:
> returns KeyValue object

Returns the specified version of a value.

```php
$item = $application->get('collection', 'key', '20c14e8965d6cbb0');
// or
$item = $collection->get('key', '20c14e8965d6cbb0');
// or
$item = new KeyValue('collection', 'key');
$item->get('20c14e8965d6cbb0');
```

### Refs List:
> returns Refs object, with results as Ref objects (a KeyValue subclass)

Get the specified version of a value.

```php
$list = $application->listRefs('collection', 'key');
// or
$list = $collection->listRefs('key');
// or
$list = new Refs('collection', 'key');
$list->listRefs();


// get array of the results (Ref objects)
$list->getResults();

// or go ahead and iterate over the results directly
foreach ($list as $item) {
    
    $item->getValue();
    // items are KeyValue objects
}

// pagination
$list->getNextUrl(); // string
$list->getPrevUrl(); // string
$list->getCount(); // count of the current set of results
$list->getTotalCount(); // count of the total results available
$list->next(); // loads next set of results
$list->prev(); // loads previous set of results
```



### Search:
> returns Search object, with results as SearchResult objects (a KeyValue subclass)

```php
$results = $application->search('collection', 'title:"The Title*"');
// or
$results = $collection->search('title:"The Title*"');
// or
$results = new Search('collection');
$results->search('title:"The Title*"');


// get array of the search results (SearchResult objects)
$list_of_items = $results->getResults();

// or go ahead and iterate over the results directly!
foreach ($results as $item) {
    
    $item->getValue();
    // items are SearchResult objects

    $item->getScore(); // search score
    $item->getDistance(); // populated if it was a Geo query
}

// aggregates
$results->getAggregates(); // array of the Aggregate results, if any 

// pagination
$results->getNextUrl(); // string
$results->getPrevUrl(); // string
$results->getCount(); // count of the current set of results
$results->getTotalCount(); // count of the total results available
$results->next(); // loads next set of results
$results->prev(); // loads previous set of results
```

All Search parameters are supported, and it includes [Geo](https://orchestrate.io/docs/apiref#geo-queries) and [Aggregates](https://orchestrate.io/docs/apiref#aggregates) queries. Please refer to the [API Reference](https://orchestrate.io/docs/apiref#search).
```php
// public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)

// aggregates example
$results = $collection->search(
    'value.created_date:[2014-01-01 TO 2014-12-31]',
    null,
    'value.created_date:time_series:month'
);
```





### Event Get
> returns Event object

```php
$event = $application->getEvent('collection', 'key', 'type', 1400684480732, 1);
// or
$event = $collection->getEvent('key', 'type', 1400684480732, 1);
// or
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event->get();
```

### Event Put (update)
> returns Event object

```php
$event = $application->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title']);
// or
$event = $collection->putEvent('key', 'type', 1400684480732, 1, ['title' => 'New Title']);
// or
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event['title'] = 'New Title';
$event->put(); // puts the whole current value, only with the title changed
$event->put(['title' => 'New Title']); // puts an entire new value
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$event = $application->putEvent('collection', 'key', 'type', 1400684480732, 1, ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$event = $collection->putEvent('key', 'type', 1400684480732, 1, ['title' => 'New Title'], '20c14e8965d6cbb0');
// or
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event['title'] = 'New Title';
$event->put(['title' => 'New Title'], '20c14e8965d6cbb0');
$event->put(['title' => 'New Title'], true); // uses the current object Ref
```


### Event Post (create)
> returns Event object

```php
$event = $application->postEvent('collection', 'key', 'type', ['title' => 'New Title']);
// or
$event = $collection->postEvent('key', 'type', ['title' => 'New Title']);
// or
$event = new Event('collection', 'key', 'type');
$event['title'] = 'New Title';
$event->post(); // posts the current Value
$event->post(['title' => 'New Title']); // posts a new value
$event->post(['title' => 'New Title'], 1400684480732); // optional timestamp
$event->post(['title' => 'New Title'], true); // use stored timestamp
```


### Event Delete
> returns Event object

Warning: Orchestrate do not support full history of each event, so the delete operation have the purge=true parameter.

```php
$event = $application->deleteEvent('collection', 'key', 'type', 1400684480732, 1);
// or
$event = $collection->deleteEvent('key', 'type', 1400684480732, 1);
// or
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
$event->delete();
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
$event = $application->deleteEvent('collection', 'key', 'type', 1400684480732, 1, '20c14e8965d6cbb0');
// or
$event = $collection->deleteEvent('key', 'type', 1400684480732, 1, '20c14e8965d6cbb0');
// or
$event = new Event('collection', 'key', 'type', 1400684480732, 1);
// first get or set a ref:
// $event->get();
// or $event->setRef('20c14e8965d6cbb0');
$event->delete(true); // delete the current ref
$event->delete('20c14e8965d6cbb0'); // delete a specific ref
```


### Event List:
> returns Events object, with results as Event objects

```php
$events = $application->listEvents('collection', 'key', 'type');
// or
$collection = new Collection('collection');
$events = $collection->listEvents('key', 'type');
// or
$events = new Events('collection', 'key', 'type'); // note the plural
$events->listEvents();


// get array of the results (Event objects)
$events->getResults();

// or go ahead and iterate over the results directly
foreach ($events as $event) {
    
    $event->getValue();
    // items are Event objects
}

// pagination
$events->getNextUrl(); // string
$events->getPrevUrl(); // string
$events->getCount(); // count of the current set of results
$events->getTotalCount(); // count of the total results available
$events->next(); // loads next set of results
$events->prev(); // loads previous set of results
```









### Graph Get (List):
> returns Graph object, with results as KeyValue objects

Returns relation's collection, key, ref, and values. The "kind" parameter(s) indicate which relations to walk and the depth to walk. Relations aren't fetched by unit, so the result will always be a List.

```php
$list = $application->listRelations('collection', 'key', 'kind');
// or
$collection = new Collection('collection');
$list = $collection->listRelations('key', 'kind');
// or
$list = new Graph('collection', 'key', 'kind');
$list->listRelations();


// the kind parameter accepts an array of strings to request the relatioship depth:
$list = $application->listRelations('collection', 'key', ['kind', 'kind2']);
// two hops


// get array of the results (KeyValue objects)
$list->getResults();

// or go ahead and iterate over the results directly
foreach ($list as $item) {
    
    $item->getValue();
    // items are KeyValue objects
}

// pagination
$list->getNextUrl(); // string
$list->getPrevUrl(); // string
$list->getCount(); // count of the current set of results
$list->getTotalCount(); // count of the total results available
$list->next(); // loads next set of results
$list->prev(); // loads previous set of results

```


### Graph Put
> returns KeyValue object

```php
$item = $application->putRelation('collection', 'key', 'kind', 'toCollection', 'toKey');
// or
$item = $collection->putRelation('key', 'kind', 'toCollection', 'toKey');
// or
$item = new KeyValue('collection', 'key');
$item->putRelation('kind', 'toCollection', 'toKey');
```


### Graph Delete
> returns KeyValue object

Deletes a relationship between two objects. Relations don't have a history, so the operation have the purge=true parameter.

```php
$item = $application->deleteRelation('collection', 'key', 'kind', 'toCollection', 'toKey');
// or
$item = $collection->deleteRelation('key', 'kind', 'toCollection', 'toKey');
// or
$item = new KeyValue('collection', 'key');
$item->deleteRelation('kind', 'toCollection', 'toKey');
```








## Docs

Please refer to the source code for now, while a proper documentation is made.

Here is a sample of the KeyValue Class methods: 

### Key/Value
```php
$item = $application->get('collection', 'key');

if ($item->isSuccess()) {
    
    // get the object info
    $item->getKey(); // string
    $item->getRef(); // string
    $item->getValue(); // array
    $item->toArray(); // array representation of the object
    $item->getBody(); // array of the HTTP response body
    
    // working with the Value
    $item['my_property']; // direct array access to the Value
    foreach ($item as $key => $value) {} // iteratable
    $item['my_property'] = 'new value'; // set
    unset($item['my_property']); // unset
    
    // some API methods
    $item->put(); // put the current value, if has changed, otherwise return
    $item->put(null); // same as above
    $item->put(['title' => 'new title']); // put a new value
    $item->delete(); // delete the current ref
    $item->delete('20c14e8965d6cbb0'); // delete the specific ref
    $item->purge(); // permanently delete all refs and graph relations

    // booleans to check status
    $item->isSuccess(); // if the last request was sucessful
    $item->isError(); // if the last request was not sucessful
    
    $item->getResponse(); // GuzzleHttp\Message\Response
    $item->getStatus(); // ok, created, items_not_found, etc
    $item->getStatusCode(); // (int) the HTTP response status code
    $item->getStatusMessage(); // Orchestrate response message, or HTTP Reason-Phrase

    $item->getRequestId(); // Orchestrate request id, X-ORCHESTRATE-REQ-ID
    $item->getRequestDate(); // the HTTP Date header
    $item->getRequestUrl(); // the effective URL that resulted in this response

}
```

Here is a sample of the Search Class methods: 

### Search
```php
$results = $application->search('collection', 'title:"The Title*"');

if ($results->isSuccess()) {
    
    // get the object info
    $results->getResults(); // array of the search results
    $results->toArray(); // array representation of the object
    $results->getBody(); // array of the full HTTP response body

    // pagination
    $results->getNextUrl(); // string
    $results->getPrevUrl(); // string
    $results->getCount(); // available to match the syntax, but is exactly the same as count($results)
    $results->getTotalCount();
    $results->next(); // loads next set of results
    $results->prev(); // loads previous set of results, if available
    
    // working with the Results
    $results[0]; // direct array access to the Results
    foreach ($results as $item) {} // iterate thought the Results
    count($results); // the Results count

    // booleans to check status
    $results->isSuccess(); // if the last request was sucessful
    $results->isError(); // if the last request was not sucessful
    
    $results->getResponse(); // GuzzleHttp\Message\Response
    $results->getStatus(); // ok, created, items_not_found, etc
    $results->getStatusCode(); // (int) the HTTP response status code
    $results->getStatusMessage(); // Orchestrate response message, or HTTP Reason-Phrase

    $results->getRequestId(); // Orchestrate request id, X-ORCHESTRATE-REQ-ID
    $results->getRequestDate(); // the HTTP Date header
    $results->getRequestUrl(); // the effective URL that resulted in this response

}
```



## Useful Notes

Here are some useful notes to consider when using the Orchestrate service:
- Avoid using slashes (/) in the key name, some problems will arise when querying them;
- If applicable, remember you can use a composite key like `{deviceID}_{sensorID}_{timestamp}` for your KeyValue keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list;
- When adding a field for a date, prefix it with '_date' or other [supported prefixes](https://orchestrate.io/docs/apiref#sorting-by-date)

