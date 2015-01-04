<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Objects\ApplicationTrait;
use andrefelipe\Orchestrate\Objects\CollectionTrait;


class Collection
{
    use ApplicationTrait, CollectionTrait;


    public function __construct(Application $application, $collection)
    {
        $this->application = $application;
        $this->collection = $collection;
    }



    /**
     * @return boolean
     */
    public function deleteCollection()
    {
        // required values
        $this->noCollectionException();

        // request
        $response = $this->application->request(
            'DELETE',
            $this->collection,
            ['query' => ['force' => 'true']]
        );

        return $response->getStatusCode() === 200;
    }






    // Cross-object API

    // Key/Value

    /**
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function get($key, $ref=null)
    {
        return $this->application->get($this->collection, $key, $ref);
    }

    /**
     * @param string $key
     * @param array $value
     * @param string $ref
     * @return KeyValue
     */
    public function put($key, array $value, $ref=null)
    {
        return $this->application->put($this->collection, $key, $value, $ref);
    }

    /**
     * @param array $value
     * @return KeyValue
     */
    public function post(array $value)
    {
        return $this->application->post($this->collection, $value);
    }

    /**
     * @param string $key
     * @param string $ref
     * @return KeyValue
     */
    public function delete($key, $ref=null)
    {
        return $this->application->delete($this->collection, $key, $ref, $purge);
    }

    /**
     * @param string $key
     * @return KeyValue
     */
    public function purge($key)
    {
        return $this->application->purge($this->collection, $key);
    }

    /**
     * @param int $limit
     * @param array $range
     * @return KeyValueList
     */
    public function listCollection($limit=10, array $range=null)
    {
        return $this->application->listCollection($this->collection, $limit, $range);
    }


    // Refs
    
    /**
     * @param string $key
     * @param int $limit
     * @param int $offset
     * @param boolean $values
     * @return Refs
     */
    public function listRefs($key, $limit=10, $offset=0, $values=false)
    {
        return $this->application->listRefs($this->collection, $key, $limit, $offset, $values);
    }


    // Events

    /**
     * @param string $key
     * @param string $type
     * @param int $limit
     * @param array $range
     * @return Events
     */
    public function listEvents($key, $type, $limit=10, array $range=null)
    {
        return $this->application->listEvents($this->collection, $key, $type, $limit, $range);
    }

    
    // Search

    /**
     * @param string $query
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return Search
     */
    public function search($query, $sort='', $limit=10, $offset=0)
    {
        return $this->application->search($this->collection, $query, $sort, $limit, $offset);
    }

}