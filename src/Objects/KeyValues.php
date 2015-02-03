<?php
namespace andrefelipe\Orchestrate\Objects;

class KeyValues extends AbstractList
{
    /**
     * @param int $limit
     * @param array $range
     * 
     * @return KeyValues self
     * @link https://orchestrate.io/docs/apiref#keyvalue-list
     */
    public function listCollection($limit=10, array $range=null)
    {
        // required values
        $this->noCollectionException();

        // define request options
        $parameters = ['limit' => $limit];

        if ($range) {
            if (isset($range['start']))
                $parameters['startKey'] = $range['start'];

            if (isset($range['after']))
                $parameters['afterKey'] = $range['after'];

            if (isset($range['before']))
                $parameters['beforeKey'] = $range['before'];

            if (isset($range['end']))
                $parameters['endKey'] = $range['end'];
        }

        // request
        $this->request('GET', $this->getCollection(), ['query' => $parameters]);
        
        return $this;
    }
}
