<?php


namespace TomasKuba\VPTree\Test;

use TomasKuba\VPTree\Element;

class ElementTest extends \PHPUnit_Framework_TestCase
{

    public function testItIsInstanceOfElementInterface()
    {
        $this->assertInstanceOf('TomasKuba\VPTree\ElementInterface', new Element(array()));
    }

    public function testTakesAndReturnsCoordinates()
    {
        $coordinates = array(1, 2, 3, 4, 5);
        $element = new Element($coordinates);

        $this->assertEquals($coordinates, $element->getCoordinates());
    }

    public function testCountElementDimensions()
    {
        $element = new Element(array(1, 2, 3, 4, 5));

        $this->assertEquals(5, $element->getDimensionsCount());
    }

    public function testReturnDimensionsAsArray()
    {
        $el = new Element(array('x' => 3, 'y' => 2, 'z' => 1));

        $this->assertEquals(array('x', 'y', 'z'), $el->getDimensions());
    }


    public function testReturnsSingleCoordinateByDimension()
    {
        $element = new Element(array(1, 2, 3, 4, 5));

        $this->assertEquals(4, $element->getCoordinate(3));
        $this->assertEquals(1, $element->getCoordinate(0));
    }
}
 