<?php


namespace TomasKuba\VPTree\Test;


use TomasKuba\VPTree\Element;
use TomasKuba\VPTree\ElementInterface;
use TomasKuba\VPTree\Node;

class NodeTest extends \PHPUnit_Framework_TestCase {

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

    protected function setup()
    {
        $this->els = array(
            new Element(array(1, 2, 3)),
            new Element(array(4, 5, 6)),
            new Element(array(7, 8, 9)),
        );
        $this->node = new Node($this->els);
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
        // Are there any other possible tests?
    }

    public function testCalculateDistance()
    {
        $el1 = new Element(array(1, 2, 3, 4, 5));
        $el2 = new Element(array(9, 8, 7, 6, 5));

        $this->assertEquals(sqrt(120), $this->node->distance($el1, $el2));
        $this->assertEquals($this->node->distance($el1, $el2), $this->node->distance($el2, $el1));
        $this->assertNotEquals(0, $this->node->distance($el1, $el2));
    }

    public function testCalculateDistanceWithNamedDimensions()
    {
        $el1 = new Element(array('x' => 3, 'y' => 2, 'z' => 1));
        $el2 = new Element(array('x' => 2, 'y' => 1, 'z' => 0));

        $this->assertEquals($this->node->distance($el1, $el2), $this->node->distance($el2, $el1));
        $this->assertNotEquals(0, $this->node->distance($el1, $el2));
    }

    /** @expectedException \InvalidArgumentException */
    public function testItThrowsOnDistanceCalculationWhenDimensionsUnequal()
    {
        $el1 = new Element(array(1, 2, 3, 4, 5));
        $el2 = new Element(array(9, 8, 7, 5));

        $this->assertEquals($this->node->distance($el1, $el2), $this->node->distance($el2, $el1));
    }


    /** @expectedException \InvalidArgumentException */
    public function testItThrowsOnDistanceCalculationWhenNamedDimensionsUnequal()
    {
        $el1 = new Element(array('z' => 3, 'y' => 2, 'x' => 1));
        $el2 = new Element(array('x' => 2, 'y' => 1, 'z' => 0));

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
        $A = new Element(array(1,1));
        $B = new Element(array(2,1)); // |AB| = 1
        $C = new Element(array(2,3)); // |BC| = 2
        $D = new Element(array(5,3)); // |CD| = 3
        $E = new Element(array(5,7)); // |DE| = 4

        $set = array($A, $B, $D, $E);
        $VPTtree = new Node($set);
        $nearestTo_C_viaBF = $this->findNearestAttributesBruteForce($C, $set);
        $this->assertEquals($B, $nearestTo_C_viaBF['element']);
        $this->assertEquals($B, $VPTtree->findNearestOne($C));
        $this->assertEquals(2, $nearestTo_C_viaBF['distance']);
        $this->assertEquals(4, $nearestTo_C_viaBF['cycles']);

        $set = array($C, $B, $D, $E);
        $VPTtree = new Node($set);
        $nearestTo_A_viaBF = $this->findNearestAttributesBruteForce($A, $set);
        $this->assertEquals($B, $nearestTo_A_viaBF['element']);
        $this->assertEquals($B, $VPTtree->findNearestOne($A));
        $this->assertEquals(1, $nearestTo_A_viaBF['distance']);
        $this->assertEquals(4, $nearestTo_A_viaBF['cycles']);
    }

    public function testFindSeveralNearestElements()
    {
        $A = new Element(array(1,1));
        $B = new Element(array(2,1)); // |AB| = 1
        $C = new Element(array(2,3)); // |BC| = 2
        $D = new Element(array(5,3)); // |CD| = 3
        $E = new Element(array(5,7)); // |DE| = 4

        $set = array($A, $B, $D, $C);
        $query = $E;
        $VPTree = new Node($set);
        $count = 30;

        $BFNearestEls = $this->findNearestAttributesBruteForce($query, $set, $count);
        $VPNearestEls = $VPTree->findNearest($query, $count);

        foreach ($BFNearestEls['elements'] as $BFElement){
            $this->assertContains($BFElement, $VPNearestEls);
        }
    }

}
 