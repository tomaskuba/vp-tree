<?php


namespace TomasKuba\VPTree;


class Node
{

    /** @var int */
    private $level;
    /** @var  array */
    private $elements = array();
    /** @var  ElementInterface */
    private $vp;
    /** @var  mixed */
    private $mu;
    /** @var  Node */
    private $innerChild;
    /** @var  Node */
    private $outerChild;
    /** @var  int */
    private $lastSearchCycles;

    function __construct(array $elements, $level = 0)
    {
        $this->level = $level;
        $this->elements = $elements;
        $this->uniqueElements();
        $this->selectVantagePoint();
        $this->calculateMu();
        if ($this->isBranch()) {
            $this->constructChildNodes();
        }
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function getElement(){
        if ($this->getElementsCount() > 0){
            return $this->elements[0];
        } else {
            return NULL;
        }

    }

    public function getVantagePoint()
    {
        return $this->vp;
    }

    public function findMedian(array $set)
    {
        sort($set, SORT_NUMERIC);
        $count = count($set);

        if ($count == 1) {
            return $set[0];
        }

        if ($count % 2) {
            $middleIndex = $count / 2;
            $median = $set[$middleIndex];
        } else {
            $lowIndex = floor(($count - 1) / 2);
            $hiIndex = ceil(($count - 1) / 2);
            $median = $set[$lowIndex] + (($set[$hiIndex] - $set[$lowIndex]) / 2);
        }

        return $median;
    }

    public function getMu()
    {
        return $this->mu;
    }

    public function getInnerChild()
    {
        return $this->innerChild;
    }

    public function getOuterChild()
    {
        return $this->outerChild;
    }

    public function getElementsCount()
    {
        return count($this->elements);
    }

    private function isLeaf()
    {
        return ($this->getElementsCount() == 1);
    }

    public function isBranch()
    {
        return !$this->isLeaf();
    }

    public function getLevel()
    {
        return $this->level;
    }

    private function uniqueElements()
    {
        if ($this->level == 0) {
            $this->elements = array_unique($this->elements, SORT_REGULAR);
        }
    }

    private function selectVantagePoint()
    {
        $this->vp = $this->elements{array_rand($this->elements)};
    }

    private function calculateMu()
    {
        $distances = array();
        /** @var ElementInterface $element */
        foreach ($this->elements as $element) {
            $distances[] = $element->distanceTo($this->vp);
        }
        $this->mu = $this->findMedian($distances);
    }

    private function constructChildNodes()
    {
        $inner = array();
        $outer = array();
        /** @var ElementInterface $element */
        foreach ($this->elements as $element) {
            if ($this->isInnerElement($element)) {
                $inner[] = $element;
            } else {
                $outer[] = $element;
            }
        }

        if (!empty($inner)) {
            $this->innerChild = new Node($inner, $this->level + 1);
        }
        if (!empty($outer)) {
            $this->outerChild = new Node($outer, $this->level + 1);
        }
    }

    /**
     * @param ElementInterface $element
     * @return bool
     */
    private function isInnerElement(ElementInterface $element)
    {
        return ($element->distanceTo($this->vp) <= $this->mu);
    }

    public function findNearestOne(ElementInterface $query){
        return $this->findNearest($query, 1)[0];
    }

    public function findNearest(ElementInterface $query, $count)
    {
        $this->lastSearchCycles = 0;
        $elementsPriorityQueue = new \SplPriorityQueue();
        $queueToSearch = new \SplPriorityQueue();

        $queueToSearch->insert($this, $this->getLevel());

        while ($queueToSearch->count() > 0){
            $this->lastSearchCycles++;
            /** @var Node $node */
            $node = $queueToSearch->extract();
            $distance = $query->distanceTo($node->getVantagePoint());

            $priority = (1 / (1 + $distance)) / ($node->getElementsCount());
            $elementsPriorityQueue->insert($node, $priority);

            if ($node->hasInnerChild()) {
                $queueToSearch->insert($node->getInnerChild(), $node->getLevel());
            }
            if ($node->hasOuterChild()) {
                $queueToSearch->insert($node->getOuterChild(), $node->getLevel());
            }
        }

        if ($count == 1) {
            return array($elementsPriorityQueue->extract()->getElement());
        }

        $elementsArray = array();
        while ($count > 0 && $elementsPriorityQueue->count() > 0) {
            $node = $elementsPriorityQueue->extract();
            if ($node->isLeaf()){
                $elementsArray[] = $node->getElement();
                $count--;
            }
        }
        return $elementsArray;

    }

    public function hasInnerChild()
    {
        return !is_null($this->innerChild);
    }

    public function hasOuterChild()
    {
        return !is_null($this->outerChild);
    }

    public function getLastSearchCycles()
    {
        return $this->lastSearchCycles;
    }
}