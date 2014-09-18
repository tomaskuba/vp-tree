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
     * @param ElementInterface $element
     * @return float
     */
    public function distanceTo(ElementInterface $element)
    {
        if ($this->getDimensions() != $element->getDimensions()){
            throw new \InvalidArgumentException('Unequal elements\' dimensions');
        }

        $sum = 0;
        foreach ($element->getCoordinates() as $d=>$v){
            $diff = abs($element->getCoordinate($d) - $this->getCoordinate($d));
            $sum += pow($diff, 2);
        }
        return sqrt($sum);
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