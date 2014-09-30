<?php


namespace TomasKuba\VPTree\Test;


use TomasKuba\VPTree\Element;
use TomasKuba\VPTree\ElementInterface;
use TomasKuba\VPTree\Node;

class NodeTest extends \PHPUnit_Framework_TestCase {
    private $a;
    private $b;
    private $c;
    private $d;
    private $e;

    /** @var  array */
    private $els;

    /** @var  Node */
    private $node;

    private function findNearestAttributesBruteForce(ElementInterface $query, array $dataSet, $count = 1)
    {
        $smallestDistance = INF;
        $nearestElement = NULL;
        $cycles = 0;
        $nearestElsQueue = new \SplPriorityQueue;
        $nearestElements = array();

        foreach ($dataSet as $element) {
            $distance = $this->node->distance($query, $element);
            if ($distance < $smallestDistance) {
                $smallestDistance = $distance;
                $nearestElement = $element;
            }
            $priority = (1 / (1 + $distance));
            $nearestElsQueue->insert($element, $priority);
            $cycles++;
        }

        while ($count > 0 && $nearestElsQueue->count() > 0){
            $nearestElements[] = $nearestElsQueue->extract();
            $count--;
        }

        return new \ArrayObject(array(
            'distance' => $smallestDistance,
            'element' => $nearestElement,
            'elements' => $nearestElements,
            'cycles' => $cycles
        ));
    }

    private function mockElement(array $coords)
    {
        return new Element($coords);
    }

    private function mockElements(){
        $arrayOfCoords = func_get_args();
        $elements = array();
        foreach ($arrayOfCoords as $coords) {
            $elements[] = $this->mockElement($coords);
        }
        return $elements;
    }

    protected function setup()
    {
        $this->els = $this->mockElements(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9)
        );
        $this->node = new Node($this->els);

        $this->a = $this->mockElement(array(1, 1));
        $this->b = $this->mockElement(array(2, 1)); // |AB| = 1
        $this->c = $this->mockElement(array(2, 3)); // |BC| = 2
        $this->d = $this->mockElement(array(5, 3)); // |CD| = 3
        $this->e = $this->mockElement(array(5, 7)); // |DE| = 4
    }

    public function testItIsInstantiableObject()
    {
        $this->assertEquals('object', gettype($this->node));
    }

    public function testItTakesAndReturnsElements()
    {
        $this->assertEquals($this->els, $this->node->getElements());
    }

    public function testItRandomlyPickAndReturnVantagePointFromElements()
    {
        $this->assertContains($this->node->getVantagePoint(), $this->els);
    }

    public function testItFindsMedianValueInUnsortedSet()
    {
        $this->assertEquals(7, $this->node->findMedian(array(3,5,9,7,22)));
        $this->assertEquals(8, $this->node->findMedian(array(3,5,9,7,22,745)));
        $this->assertEquals(17.5, $this->node->findMedian(array(5,15,20,25)));
    }

    public function testItCalculatesAndReturnAnyMuValue()
    {
        $this->assertNotEmpty($this->node->getMu());
        $this->assertNotEquals(0, $this->node->getMu());
    }

    public function testCalculateDistance()
    {
        $el1 = $this->mockElement(array(1, 2, 3, 4, 5));
        $el2 = $this->mockElement(array(9, 8, 7, 6, 5));

        $this->assertEquals(sqrt(120), $this->node->distance($el1, $el2));
        $this->assertEquals($this->node->distance($el1, $el2), $this->node->distance($el2, $el1));
        $this->assertNotEquals(0, $this->node->distance($el1, $el2));
    }

    public function testCalculateDistanceWithNamedDimensions()
    {
        $el1 = $this->mockElement(array('x' => 3, 'y' => 2, 'z' => 1));
        $el2 = $this->mockElement(array('x' => 2, 'y' => 1, 'z' => 0));

        $this->assertEquals($this->node->distance($el1, $el2), $this->node->distance($el2, $el1));
        $this->assertNotEquals(0, $this->node->distance($el1, $el2));
    }

    /** @expectedException \InvalidArgumentException */
    public function testItThrowsOnDistanceCalculationWhenDimensionsUnequal()
    {
        $el1 = $this->mockElement(array(1, 2, 3, 4, 5));
        $el2 = $this->mockElement(array(9, 8, 7, 5));

        $this->assertEquals($this->node->distance($el1, $el2), $this->node->distance($el2, $el1));
    }


    /** @expectedException \InvalidArgumentException */
    public function testItThrowsOnDistanceCalculationWhenNamedDimensionsUnequal()
    {
        $el1 = $this->mockElement(array('z' => 3, 'y' => 2, 'x' => 1));
        $el2 = $this->mockElement(array('x' => 2, 'y' => 1, 'z' => 0));

        $this->assertEquals($this->node->distance($el1, $el2), $this->node->distance($el2, $el1));
    }

    public function testItSetsAndReturnChildNodesFromElements()
    {
        $elsCount = count($this->els);
        $innerCount = $this->node->hasInnerChild() ? $this->node->getInnerChild()->getElementsCount() : 0;
        $outerCount = $this->node->hasOuterChild() ? $this->node->getOuterChild()->getElementsCount() : 0;

        $this->assertEquals($elsCount, ($innerCount + $outerCount));
    }

    public function testItHandlesNonUniqueSetOfInitialElements()
    {
        $elsPlusDuplicates = $this->els;
        $elsPlusDuplicates[] = $this->els[1];
        $elsPlusDuplicates[] = $this->els[0];
        $node = new Node($elsPlusDuplicates);

        $this->assertNotEquals(count($elsPlusDuplicates), $node->getElementsCount());
        $this->assertNotEquals(count($this->els) + 2, $node->getElementsCount());
    }

    public function testItIsNumberingTreeLevels()
    {
        if (!is_null($this->node->getInnerChild())) {
            $childNode = $this->node->getInnerChild();
        } else {
            $childNode = $this->node->getOuterChild();
        }

        $this->assertEquals(0, $this->node->getLevel());
        $this->assertEquals(1, $childNode->getLevel());
    }

    public function testFindOneNearestElement()
    {
        $set = array($this->a, $this->b, $this->d, $this->e);
        $VPTree = new Node($set);

        $BFNearestEls = $this->findNearestAttributesBruteForce($this->c, $set);
        $VPNearest = $VPTree->findNearestOne($this->c);

        $this->assertEquals($this->b, $BFNearestEls['element']);
        $this->assertEquals($this->b, $VPNearest);
        $this->assertEquals(2, $BFNearestEls['distance']);
        $this->assertEquals(4, $BFNearestEls['cycles']);
    }

    public function testFindSeveralNearestElements()
    {
        $set = array($this->a, $this->b, $this->d, $this->c);
        $query = $this->e;
        $VPTree = new Node($set);
        $count = 30;

        $BFNearestEls = $this->findNearestAttributesBruteForce($query, $set, $count);
        $VPNearestEls = $VPTree->findNearest($query, $count);

        foreach ($BFNearestEls['elements'] as $BFElement){
            $this->assertContains($BFElement, $VPNearestEls);
        }
    }


}
 