<?php


namespace TomasKuba\VPTree\Test;


use TomasKuba\VPTree\Element;
use TomasKuba\VPTree\Node;

class NodeTest extends \PHPUnit_Framework_TestCase {

    /** @var  array */
    private $els;
    /** @var  Node */
    private $node;

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

    public function testItSetsAndReturnChildNodesFromElements()
    {
        $elsCount = count($this->els);
        $innerNode = $this->node->getInnerChild();
        $innerCount = is_null($innerNode) ? 0 : $innerNode->getElementsCount();
        $outerNode = $this->node->getOuterChild();
        $outerCount = is_null($outerNode) ? 0 : $outerNode->getElementsCount();

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

}
 