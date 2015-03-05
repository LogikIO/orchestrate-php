<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate\Objects\Properties\CollectionTrait;
use andrefelipe\Orchestrate\Objects\Properties\KeyTrait;
use andrefelipe\Orchestrate\Objects\Properties\RefTrait;
use andrefelipe\Orchestrate\Query\PatchBuilder;

class KeyValue extends AbstractObject
{
    use CollectionTrait;
    use KeyTrait;
    use RefTrait;

    public function __construct($collection = null, $key = null)
    {
        $this->setCollection($collection);
        $this->setKey($key);
    }

    public function reset()
    {
        parent::reset();
        $this->_key = null;
        $this->_ref = null;
        $this->resetValue();
    }

    public function init(array $values)
    {        
        if (empty($values)) {
            return;
        }            

        if (!empty($values['path'])) {
            $values = array_merge($values, $values['path']);
        }

        foreach ($values as $key => $value) {
            
            if ($key === 'collection')
                $this->setCollection($value);

            elseif ($key === 'key')
                $this->setKey($value);

            elseif ($key === 'ref')
                $this->setRef($value);

            elseif ($key === 'value')
                $this->setValue((array) $value);
        }

        return $this;
    }

    public function toArray()
    {
        $result = [
            'kind' => 'item',
            'path' => [
                'collection' => $this->getCollection(),
                'key' => $this->getKey(),
                'ref' => $this->getRef(),
            ],
            'value' => parent::toArray(),
        ];
        
        return $result;
    }

    private $graph;
    public function relations()
    {
        if (!$graph) {
            $graph = (new Graph())
                ->setApplication($this->getApplication(true))
                ->setCollection($this->getCollection(true))
                ->setKey($this->getKey(true));
        }
        return $graph;
    }

    /**
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-get
     */
    public function get($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);

        if ($ref) {
            $path .= '/refs/'.trim($ref, '"');
        }

        // request
        $this->request('GET', $path);

        // set values
        $this->resetValue();
        $this->_ref = null;

        if ($this->isSuccess()) {
            $this->setValue($this->getBody());
            $this->setRefFromETag();
        }

        return $this->isSuccess();
    }    
    
    /**
     * @param array $value
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-put
     */
    public function put(array $value = null, $ref = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $newValue];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];

        } elseif ($ref === false) {

            // set If-None-Match
            $options['headers'] = ['If-None-Match' => '"*"'];
        }

        // request
        $this->request('PUT', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            if ($value !== null) {
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param array $value
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-post
     */
    public function post(array $value = null)
    {
        $newValue = $value === null ? parent::toArray() : $value;

        // request
        $this->request('POST', $this->getCollection(true), ['json' => $newValue]);
        
        // set values
        if ($this->isSuccess()) {
            $this->_key = null;
            $this->_ref = null;
            $this->setKeyRefFromLocation();
            if ($value !== null) {
                $this->setValue($newValue);
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param PatchBuilder $operations
     * @param string $ref
     * @param boolean $reload
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch
     */
    public function patch(PatchBuilder $operations, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $operations->toArray()];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('PATCH', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            // reload the Value from API
            if ($reload) {
                $this->get($this->getRef());
            }
        }
        
        return $this->isSuccess();
    }

    /**
     * @param array $value
     * @param string $ref
     * @param boolean $reload
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-patch-merge
     */
    public function patchMerge(array $value, $ref = null, $reload = false)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['json' => $value];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('PATCH', $path, $options);
        
        // set values
        if ($this->isSuccess()) {
            $this->setRefFromETag();

            // reload the Value from API
            if ($reload) {
                $this->get($this->getRef());
            }
        }

        return $this->isSuccess();
    }

    /**
     * @param string $ref
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function delete($ref = null)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = [];

        if ($ref) {

            // set If-Match
            if ($ref === true) {
                $ref = $this->getRef();
            }

            $options['headers'] = ['If-Match' => '"'.$ref.'"'];
        }

        // request
        $this->request('DELETE', $path, $options);

        return $this->isSuccess();
    }

    /**
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#keyvalue-delete
     */
    public function purge()
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true);
        $options = ['query' => ['purge' => 'true']];

        // request
        $this->request('DELETE', $path, $options);
        
        // null ref if success, as it will never exist again
        if ($this->isSuccess()) {
            $this->_ref = null;
        }

        return $this->isSuccess();
    }

    /**
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-put
     */
    public function putRelation($kind, $toCollection, $toKey)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;
        
        // request
        $this->request('PUT', $path);
        
        return $this->isSuccess();
    }
    
    /**
     * @param string $kind
     * @param string $toCollection
     * @param string $toKey
     * 
     * @return boolean Success of operation.
     * @link https://orchestrate.io/docs/apiref#graph-delete
     */
    public function deleteRelation($kind, $toCollection, $toKey)
    {
        // define request options
        $path = $this->getCollection(true).'/'.$this->getKey(true)
            .'/relation/'.$kind.'/'.$toCollection.'/'.$toKey;

        // request
        $this->request('DELETE', $path, ['query' => ['purge' => 'true']]);
        
        return $this->isSuccess();
    }
    
    /**
     * Helper to set the Key and Ref from a Orchestrate Location HTTP header.
     * For example: Location: /v0/collection/key/refs/ad39c0f8f807bf40
     */
    private function setKeyRefFromLocation()
    {
        $location = $this->getResponse()->getHeader('Location');
        if (!$location) {
            $location = $this->getResponse()->getHeader('Content-Location');
        }

        $location = explode('/', trim($location, '/'));
        if (count($location) > 4)
        {
            $this->setKey($location[2]);
            $this->setRef($location[4]);
        }
    }
}
