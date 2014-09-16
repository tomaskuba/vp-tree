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
}