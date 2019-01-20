<?php


namespace TomasKuba\VPTree;


class Element implements ElementInterface
{

    /** @var array */
    protected $coordinates;

    /** @var array  */
    protected $payload;

    /**
     * @param array $coordinates
     */
    function __construct(array $coordinates  = [], array $payload  = [])
    {
        $this->coordinates = $coordinates;
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     * @return \TomasKuba\VPTree\Element $this Chainable
     */
    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @param array $item
     * @return \TomasKuba\VPTree\Element $this Chainable
     */
    public function addPayload($item){
        $this->payload[] = $item;
        return $this;
    }
    /**
     * @return array
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @param array $coordinates
     * @return \TomasKuba\VPTree\Element $this Chainable
     */
    public function setCoordinates($coordinates)
    {
        $this->coordinates = $coordinates;
        return $this;
    }

}