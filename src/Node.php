<?php


namespace TomasKuba\VPTree;


class Node
{

    /** @var int */
    private $level;

    /** @var  array */
    private $elements = [];

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

    public function __construct(array $elements, $level = 0)
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
        $distances = [];

        /** @var ElementInterface $element */
        foreach ($this->elements as $element) {
            $distances[] = $this->distance($element, $this->vp);
        }

        $this->mu = $this->findMedian($distances);
    }

    public function distance(ElementInterface $a, ElementInterface $b)
    {
        $aCoordinates = $a->getCoordinates();
        $bCoordinates = $b->getCoordinates();

        if (array_keys($aCoordinates) != array_keys($bCoordinates)) {
            throw new \InvalidArgumentException('Unequal elements\' dimensions');
        }

        $sum = 0;
        foreach ($aCoordinates as $dimension => $value) {
            $diff = abs($bCoordinates[$dimension] - $aCoordinates[$dimension]);
            $sum += pow($diff, 2);
        }
        $distance = sqrt($sum);

        return $distance;
    }

    public function findMedian(array $set)
    {
        $count = count($set);

        sort($set, SORT_NUMERIC);

        if ($count == 1) {
            return $set[0];
        }

        if ($count % 2) {
            return $set[$count / 2];
        }

        $lowIndex = floor(($count - 1) / 2);
        $hiIndex = ceil(($count - 1) / 2);

        return $set[$lowIndex] + (($set[$hiIndex] - $set[$lowIndex]) / 2);

    }

    private function constructChildNodes()
    {
        $inner = [];
        $outer = [];

        /** @var ElementInterface $element */
        foreach ($this->elements as $element) {
            if ($this->isElementInInnerChild($element)) {
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

    private function isElementInInnerChild(ElementInterface $element)
    {
        return ($this->distance($element, $this->vp) <= $this->mu);
    }

    public function findNearestOne(ElementInterface $query)
    {
        $nearest = $this->findNearest($query, 1);

        return $nearest[0];
    }

    public function findNearest(ElementInterface $query, $count)
    {
        return $this->extractNearestByCount($this->findNearestElements($query), $count);

    }

    private function findNearestElements(ElementInterface $query)
    {
        $this->lastSearchCycles = 0;
        $elementsQueue = new \SplPriorityQueue();
        $searchQueue = new \SplPriorityQueue();
        $searchQueue->insert($this, $this->getLevel());

        while ($searchQueue->count() > 0) {
            $this->lastSearchCycles++;
            /** @var Node $node */
            $node = $searchQueue->extract();
            $distance = $this->distance($query, $node->getVantagePoint());
            $priority = (1 / (1 + $distance)) / ($node->getElementsCount());
            $elementsQueue->insert($node, $priority);

            if ($node->hasInnerChild()) {
                $searchQueue->insert($node->getInnerChild(), $node->getLevel());
            }

            if ($node->hasOuterChild()) {
                $searchQueue->insert($node->getOuterChild(), $node->getLevel());
            }
        }

        return $elementsQueue;
    }

    private function extractNearestByCount(\SplPriorityQueue $elementsQueue, $count)
    {
        if ($count == 1) {
            return [$elementsQueue->extract()->getElement()];
        }

        $elementsArray = [];
        while ($count > 0 && $elementsQueue->count() > 0) {
            $node = $elementsQueue->extract();
            if ($node->isLeaf()) {
                $elementsArray[] = $node->getElement();
                $count--;
            }
        }

        return $elementsArray;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function getVantagePoint()
    {
        return $this->vp;
    }

    public function getMu()
    {
        return $this->mu;
    }

    public function hasInnerChild()
    {
        return !is_null($this->innerChild);
    }

    public function getInnerChild()
    {
        return $this->innerChild;
    }

    public function hasOuterChild()
    {
        return !is_null($this->outerChild);
    }

    public function getOuterChild()
    {
        return $this->outerChild;
    }

    public function getLastSearchCycles()
    {
        return $this->lastSearchCycles;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function getElement()
    {
        if ($this->getElementsCount() > 0) {
            return $this->elements[0];
        } else {
            return null;
        }
    }

    public function getElementsCount()
    {
        return count($this->elements);
    }

    public function isLeaf()
    {
        return ($this->getElementsCount() == 1);
    }

    public function isBranch()
    {
        return !$this->isLeaf();
    }
}