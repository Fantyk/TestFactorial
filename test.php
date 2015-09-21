<?php

class PrimeNumberFinder
{
    public function get($limit)
    {
        $result = array_fill(2, $limit - 1, true);
        $sqrt = floor(sqrt($limit));
        for ($i = 2; $i <= $sqrt; $i++) {
            if ($result[$i] === true) {
                for ($j = $i * $i; $j <= $limit; $j += $i) {
                    $result[$j] = false;
                }
            }
        }
        foreach ($result as $id => $isPrimeNumber) {
            if (!$isPrimeNumber) {
                unset($result[$id]);
            }
        }
        return array_keys($result);
    }
}

class Factor
{
    /**
     * @var PrimeNumberFinder
     */
    private $primeNumberFinder;
    private $primeNumbers;
    private $num;
    private $factors;

    public function __construct(PrimeNumberFinder $primeNumberFinder)
    {
        $this->primeNumberFinder = $primeNumberFinder;
    }

    public function setPrimeNumbersLimit($limit)
    {
        $this->primeNumbers = $this->primeNumberFinder->get($limit);
    }

    public function getPrimeNumbers()
    {
        return $this->primeNumbers;
    }

    public function factorize($num)
    {
        $this->num = $num;
        $this->factors = [];
        $this->setPrimeNumbers($num);
        $run = true;
        while ($run) {
            $run = $this->getFactors();
        }
        return array_count_values($this->factors);
    }

    private function getFactors()
    {
        if ($this->num == 1) {
            return false;
        }
        $root = ceil(sqrt($this->num)) + 1;
        $i = 0;
        while ($this->primeNumbers[$i] <= $root) {
            $calc = $this->num / $this->primeNumbers[$i];
            $rounded = (int)$calc;
            if ($rounded - $calc == 0) {
                $this->factors[] = $this->primeNumbers[$i];
                $this->num = $rounded;
                return true;
            }
            $i++;
        }
        $this->factors[] = $this->num;
        return false;
    }

    private function setPrimeNumbers($num)
    {
        if (!isset($this->primeNumbers) || $num > end($this->primeNumbers)) {
            $this->setPrimeNumbersLimit($num);
        }
    }
}

class App
{
    /**
     * @var Factor
     */
    private $factor;

    public function __construct(Factor $factor, $limit = 100000)
    {
        $this->factor = $factor;
        $this->factor->setPrimeNumbersLimit($limit);
    }

    /**
     *
     * @param $n
     * @param $divider
     * @return bool  если $n! делится на $divider без остатка возвращает true, иначе false
     * @throws \InvalidArgumentException
     */
    public function execute($n, $divider)
    {
        if (((int)$n != $n) || ((int)$divider != $divider) || $n < 1 || $divider < 1) {
            throw new \InvalidArgumentException("wrong input params");
        }
        if ($n >= $divider) {
            return true;
        }
        if ($this->isPrimeNumber($divider)) {
            return false;
        }
        $factorsDivider = $this->factor->factorize($divider);
        $i = 1;
        while ($i <= $n) {
            $factorsMultipliers = $this->factor->factorize($i);
            $this->applyFactorsMultipliers($factorsDivider, $factorsMultipliers);
            if ($this->isEnoughFactors($factorsDivider)) {
                return true;
            }
            $i++;
        }
        return false;
    }

    private function applyFactorsMultipliers(&$factorsDivider, $factorsMultiplier)
    {
        foreach ($factorsMultiplier as $primeNumber => $count) {
            if (isset($factorsDivider[$primeNumber])) {
                $factorsDivider[$primeNumber] -= $count;
            } else {
                $factorsDivider[$primeNumber] = -$count;
            }
        }
    }

    private function isEnoughFactors($factorsDivider)
    {
        foreach ($factorsDivider as $primeNumber => $count) {
            if ($count > 0) {
                return false;
            }
        }
        return true;
    }

    private function isPrimeNumber($number)
    {
        return in_array($number, $this->factor->getPrimeNumbers());
    }
}

$primeNumbersFinder = new PrimeNumberFinder();
$factor = new Factor($primeNumbersFinder);
$app = new App($factor);
$testCases = [
    ['input' => [1, 1], 'output' => true],
    ['input' => [1, 2], 'output' => false],
    ['input' => [2, 1], 'output' => true],
    ['input' => [6, 10], 'output' => true],
    ['input' => [13, 25], 'output' => true],
    ['input' => [13, 13], 'output' => true],
    ['input' => [13, 20], 'output' => true],
    ['input' => [13, 17], 'output' => false],
    ['input' => [34, 137], 'output' => false],
    ['input' => [100000, 99971], 'output' => true],
    ['input' => [105000, 99970], 'output' => true]
];
foreach ($testCases as $case) {
    if ($app->execute($case['input'][0], $case['input'][1]) !== $case['output']) {
        throw new \Exception(sprintf("fail with input params %s, %s", $case['input'][0], $case['input'][1]));
    }
}
