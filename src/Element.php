<?php


namespace TomasKuba\VPTree;


class Element implements ElementInterface {

    /**
     * @var array
     */
    private $coordinates = array();

    /**
     * @param array $coordinates
     */
    function __construct(array $coordinates)
    {
        $this->coordinates = $coordinates;
    }

    /**
     * @return array
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @return mixed
     */
    public function getDimensions()
    {
        return array_keys($this->coordinates);
    }

    /**
     * @return int
     */
    public function getDimensionsCount()
    {
        return count($this->coordinates);
    }

    /**
     * @param mixed $dimension
     * @return mixed
     */
    public function getCoordinate($dimension)
    {
        return $this->coordinates{$dimension};
    }


}