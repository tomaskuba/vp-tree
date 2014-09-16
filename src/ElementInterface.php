<?php


namespace TomasKuba\VPTree;


interface ElementInterface {

    /**
     * @return mixed
     */
    public function getCoordinates();

    /**
     * @return mixed
     */
    public function getDimensions();

    /**
     * @return int
     */
    public function getDimensionsCount();

    /**
     * @param mixed $dimension
     * @return mixed
     */
    public function getCoordinate($dimension);

    /**
     * @param ElementInterface $element
     * @return mixed
     */
    public function distanceTo(ElementInterface $element);
} 