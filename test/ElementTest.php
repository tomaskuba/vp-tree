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

    public function testCalculateDistance()
    {
        $el1 = new Element(array(1, 2, 3, 4, 5));
        $el2 = new Element(array(9, 8, 7, 6, 5));

        $this->assertEquals(sqrt(120), $el1->distanceTo($el2));
        $this->assertEquals($el1->distanceTo($el2), $el2->distanceTo($el1));
        $this->assertNotEquals(0, $el1->distanceTo($el2));
    }

    public function testCalculateDistanceWithNamedDimensions()
    {
        $el1 = new Element(array('x' => 3, 'y' => 2, 'z' => 1));
        $el2 = new Element(array('x' => 2, 'y' => 1, 'z' => 0));

        $this->assertEquals($el1->distanceTo($el2), $el2->distanceTo($el1));
        $this->assertNotEquals(0, $el1->distanceTo($el2));
    }

    /** @expectedException \InvalidArgumentException */
    public function testItThrowsOnDistanceCalculationWhenDimensionsAreUnequal()
    {
        $el1 = new Element(array(1, 2, 3, 4, 5));
        $el2 = new Element(array(9, 8, 7, 5));

        $this->assertEquals($el1->distanceTo($el2), $el2->distanceTo($el1));

        $el1 = new Element(array('z' => 3, 'y' => 2, 'x' => 1));
        $el2 = new Element(array('x' => 2, 'y' => 1, 'z' => 0));

        $this->assertEquals($el1->distanceTo($el2), $el2->distanceTo($el1));
    }
}
 