<?php

namespace Rubix\Tensor;

use InvalidArgumentException;
use RuntimeException;
use ArrayIterator;

/**
 * Vector
 *
 * One dimensional tensor with integer and/or floating point elements.
 *
 * @category    Linear Algebra
 * @package     Rubix/Tensor
 * @author      Andrew DalPino
 */
class Vector implements Tensor
{
    /**
     * The 1-d array that holds the values of the vector.
     *
     * @var array
     */
    protected $a = [
        //
    ];

    /**
     * The number of elements in the vector.
     *
     * @var int
     */
    protected $n;

    /**
     * Factory method to build a new vector from an array.
     *
     * @param  (int|float)[]  $a
     * @return self
     */
    public static function build(array $a = []) : self
    {
        return new self($a, true);
    }

    /**
     * Build a vector foregoing any validation for quicker instantiation.
     *
     * @param  (int|float)[]  $a
     * @return self
     */
    public static function quick(array $a = []) : self
    {
        return new self($a, false);
    }

    /**
     * Build a vector of zeros with n elements.
     *
     * @param  int  $n
     * @return self
     */
    public static function zeros(int $n) : self
    {
        return new self(array_fill(0, $n, 0), false);
    }

    /**
     * Build a vector of ones with n elements.
     *
     * @param  int  $n
     * @return self
     */
    public static function ones(int $n) : self
    {
        return new self(array_fill(0, $n, 1), false);
    }

    /**
     * Fill a vector with a given value.
     *
     * @param  mixed  $value
     * @throws \InvalidArgumentException
     * @return self
     */
    public static function fill($value, int $n) : self
    {
        if (!is_int($value) and !is_float($value)) {
            throw new InvalidArgumentException('Fill value must be an integer'
                . ' or float, ' . gettype($value) . ' found.');
        }

        return new self(array_fill(0, $n, $value), false);
    }

    /**
     * Return a random uniform vector with values between 0 and 1.
     *
     * @param  int  $n
     * @return self
     */
    public static function rand(int $n) : self
    {
        $a = [];

        for ($i = 0; $i < $n; $i++) {
            $a[] = rand(0, PHP_INT_MAX) / PHP_INT_MAX;
        }

        return new self($a, false);
    }

    /**
     * Return a standard normally distributed random vector i.e values between
     * -1 and 1.
     *
     * @param  int  $n
     * @return self
     */
    public static function gaussian(int $n) : self
    {
        $a = [];

        for ($i = 0; $i < $n; $i++) {
            $r1 = rand(0, PHP_INT_MAX) / PHP_INT_MAX;
            $r2 = rand(0, PHP_INT_MAX) / PHP_INT_MAX;

            $a[] = sqrt(-2. * log($r1)) * cos(self::TWO_PI * $r2);
        }

        return new self($a, false);
    }

    /**
     * Return a uniform random vector with mean 0 and unit variance.
     *
     * @param  int  $m
     * @param  int  $n
     * @return self
     */
    public static function uniform(int $m, int $n) : self
    {
        $a = [];

        for ($i = 0; $i < $n; $i++) {
            $a[] = rand(-PHP_INT_MAX, PHP_INT_MAX) / PHP_INT_MAX;
        }

        return new self($a, false);
    }

    /**
     * Return evenly spaced values within a given interval.
     *
     * @param  float  $start
     * @param  float  $end
     * @param  float  $interval
     * @throws \InvalidArgumentException
     * @return self
     */
    public static function range(float $start, float $end, float $interval = 1.) : self
    {
        if ($interval <= 0.) {
            throw new InvalidArgumentException('Interval must be greater than'
                 . ' 0.');
        }

        return new self(range($start, $end, $interval), false);
    }

    /**
     * Return evenly spaced numbers over a specified interval.
     *
     * @param  float  $start
     * @param  float  $end
     * @param  int  $n
     * @return self
     */
    public static function linspace(float $start, float $end, int $n) : self
    {
        $interval = abs($end - $start) / ($n - 1);

        return self::range($start, $end, $interval);
    }

    /**
     * @param  (int|float)[]  $a
     * @param  bool  $validate
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __construct(array $a = [], bool $validate = true)
    {
        if ($validate === true) {
            $a = array_values($a);

            foreach ($a as $value) {
                if (!is_int($value) and !is_float($value)) {
                    throw new InvalidArgumentException('Vector element must'
                        . ' be an integer or float, '
                        . gettype($value) . ' found.');
                }
            }
        }

        $this->a = $a;
        $this->n = count($a);
    }

    /**
     * Return a tuple with the dimensionality of the tensor.
     *
     * @return int[]
     */
    public function shape() : array
    {
        return [$this->n];
    }


    /**
     * Return the number of elements in the vector.
     *
     * @return int
     */
    public function size() : int
    {
        return $this->n;
    }

    /**
     * Return the number of columns in the vector.
     *
     * @return int
     */
    public function n() : int
    {
        return $this->n;
    }

    /**
     * Return the vector as an array.
     *
     * @return array
     */
    public function asArray() : array
    {
        return $this->a;
    }

    /**
     * Return this vector as a row matrix.
     *
     * @return \Rubix\Tensor\Matrix
     */
    public function asRowMatrix() : Matrix
    {
        return new Matrix([$this->a], false);
    }

    /**
     * Return this vector as a column matrix.
     *
     * @return \Rubix\Tensor\Matrix
     */
    public function asColumnMatrix() : Matrix
    {
        return new Matrix(array_map(function ($value) {
            return [$value];
        }, $this->a), false);
    }

    /**
     * Return a matrix in the shape specified.
     *
     * @param  int  $m
     * @param  int  $n
     * @throws \InvalidArgumentException
     * @return \Rubix\Tensor\Matrix
     */
    public function reshape(int $m, int $n) : Matrix
    {
        if (($m * $n) !== $this->size()) {
            throw new InvalidArgumentException('The shape of the new matrix is'
                . ' incompatible with the current vector.');
        }

        $index = 0;

        $b = [[]];

        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $b[$i][$j] = $this->a[$index++];
            }
        }

        return new Matrix($b, false);
    }

    /**
     * Map a function over the elements in the vector and return a new vector.
     *
     * @param  callable  $fn
     * @param  bool  $validate
     * @return self
     */
    public function map(callable $fn, bool $validate = false) : self
    {
        return new self(array_map($fn, $this->a), false);
    }

    /**
     * Reduce the vector down to a scalar.
     *
     * @param  callable  $fn
     * @param  float  $initial
     * @return float
     */
    public function reduce(callable $fn, float $initial = 0.) : float
    {
        return array_reduce($this->a, $fn, $initial);
    }

    /**
     * Compute the dot product of this vector and another vector.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @throws \InvalidArgumentException
     * @return float
     */
    public function dot(Vector $b) : float
    {
        if ($this->n !== $b->size()) {
            throw new InvalidArgumentException('Vectors do not have the same'
                . ' dimensionality.');
        }

        $product = 0.;

        foreach ($this->a as $i => $value) {
            $product += $value * $b[$i];
        }

        return $product;
    }

    /**
     * Return the inner product of two vectors. Alias of dot().
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @return float
     */
    public function inner(Vector $b) : float
    {
        return $this->dot($b);
    }

    /**
     * Calculate the outer product of this and another vector. Return as Matrix.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @return \Rubix\Tensor\Matrix
     */
    public function outer(Vector $b) : Matrix
    {
        $n = $b->n();

        $c = [[]];

        foreach ($this->a as $i => $value) {
            for ($j = 0; $j < $n; $j++) {
                $c[$i][$j] = $value * $b[$j];
            }
        }

        return new Matrix($c);
    }

    /**
     * Calculate the cross product between two 3 dimensional vectors.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @return self
     */
    public function cross(Vector $b) : self
    {
        if ($this->n !== 3 or $b->n() !== 3) {
            throw new InvalidArgumentException('Cross product is only defined'
                . ' for vectors in 3 dimensions.');
        }

        $c = [];

        $c[] = ($this->a[1] * $b[2]) - ($this->a[2] * $b[1]);
        $c[] = ($this->a[2] * $b[0]) - ($this->a[0] * $b[2]);
        $c[] = ($this->a[0] * $b[1]) - ($this->a[1] * $b[0]);

        return new self($c, false);
    }

    /**
     * Multiply this vector with another tensor elementwise.
     *
     * @param  mixed  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function multiply($b) : self
    {
        if ($b instanceof Vector) {
            return $this->multiplyVector($b);
        } else if (is_int($b) or is_float($b)) {
            return $this->multiplyScalar($b);
        }

        throw new InvalidArgumentException('Cannot multiply vector with a'
            . ' ' . gettype($b) . '.');
    }

    /**
     * Divide this vector with another tensor elementwise.
     *
     * @param  mixed  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function divide($b) : self
    {
        if ($b instanceof Vector) {
            return $this->divideVector($b);
        } else if (is_int($b) or is_float($b)) {
            return $this->divideScalar($b);
        }

        throw new InvalidArgumentException('Cannot multiply vector with a'
            . ' ' . gettype($b) . '.');
    }

    /**
     * Add this vector to another tensor elementwise.
     *
     * @param  mixed  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function add($b) : self
    {
        if ($b instanceof Vector) {
            return $this->addVector($b);
        } else if (is_int($b) or is_float($b)) {
            return $this->addScalar($b);
        }

        throw new InvalidArgumentException('Cannot add vector to a'
            . ' ' . gettype($b) . '.');
    }

    /**
     * Add this vector to another tensor elementwise.
     *
     * @param  mixed  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function subtract($b) : self
    {
        if ($b instanceof Vector) {
            return $this->subtractVector($b);
        } else if (is_int($b) or is_float($b)) {
            return $this->subtractScalar($b);
        }

        throw new InvalidArgumentException('Cannot subtract vector from a'
            . ' ' . gettype($b) . '.');
    }

    /**
     * Raise this vector to the power of another tensor elementwise.
     *
     * @param  mixed  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function pow($b) : self
    {
        if ($b instanceof Vector) {
            return $this->powVector($b);
        } else if (is_int($b) or is_float($b)) {
            return $this->powScalar($b);
        }

        throw new InvalidArgumentException('Cannot multiply vector with a'
            . ' ' . gettype($b) . '.');
    }

    /**
     * Multiply this vector with another vector.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function multiplyVector(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vector dimensionality does not'
                . ' match.' . (string) $this->n . ' needed but'
                . ' found ' . (string) $b->n() . '.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value * $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Divide this vector by another vector.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function divideVector(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vector dimensionality does not'
                . ' match.' . (string) $this->n . ' needed but'
                . ' found ' . (string) $b->n() . '.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value / $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Add this vector to another vector.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function addVector(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vector dimensionality does not'
                . ' match.' . (string) $this->n . ' needed but'
                . ' found ' . (string) $b->n() . '.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value + $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Subtract this vector from another vector.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function subtractVector(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vector dimensionality does not'
                . ' match.' . (string) $this->n . ' needed but'
                . ' found ' . (string) $b->n() . '.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value - $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Raise this vector to a power of another vector.
     *
     * @param  \Rubix\Tensor\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function powVector(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vector dimensionality does not'
                . ' match.' . (string) $this->n . ' needed but'
                . ' found ' . (string) $b->n() . '.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value - $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Multiply this vector by a scalar.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function multiplyScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Scalar must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value * $scalar;
        }

        return new self($b, false);
    }

    /**
     * Divide this vector by a scalar.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function divideScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Scalar must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value / $scalar;
        }

        return new self($b, false);
    }

    /**
     * Add a scalar to this vector.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function addScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Factor must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value + $scalar;
        }

        return new self($b, false);
    }

    /**
     * Subtract a scalar from this vector.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function subtractScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Scalar must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value - $scalar;
        }

        return new self($b, false);
    }

    /**
     * Raise the vector to a the power of a scalar value.
     *
     * @param  int|float  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function powScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Scalar must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $value) {
            $b[] = $value ** $scalar;
        }

        return new self($b, false);
    }

    /**
     * Take the absolute value of the vector.
     *
     * @return self
     */
    public function abs() : self
    {
        return new self(array_map('abs', $this->a), false);
    }

    /**
     * Square the vector.
     *
     * @return self
     */
    public function square() : self
    {
        return $this->pow(2);
    }

    /**
     * Take the square root of the vector.
     *
     * @return self
     */
    public function sqrt() : self
    {
        return new self(array_map('sqrt', $this->a), false);
    }

    /**
     * Exponentiate each element in the vector.
     *
     * @return self
     */
    public function exp() : self
    {
        return new self(array_map('exp', $this->a), false);
    }

    /**
     * Exponentiate each element in the vector.
     *
     * @param  float  $base
     * @return self
     */
    public function log(float $base = M_E) : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = log($value, $base);
        }

        return new self($b, false);
    }

    /**
     * Return the sine of this vector.
     *
     * @return self
     */
    public function sin() : self
    {
        return new self(array_map('sin', $this->a), false);
    }

    /**
     * Return the cosine of this vector.
     *
     * @return self
     */
    public function cos() : self
    {
        return new self(array_map('cos', $this->a), false);
    }

    /**
     * Negate the vector i.e take the negative of each value elementwise.
     *
     * @return self
     */
    public function negate() : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = -$value;
        }

        return new self($b, false);
    }

    /**
     * The sum of the vector.
     *
     * @return float
     */
    public function sum() : float
    {
        return (float) array_sum($this->a);
    }

    /**
     * Return the product of the vector.
     *
     * @return float
     */
    public function product() : float
    {
        return (float) array_product($this->a);
    }

    /**
     * Return the minimum element in the vector.
     *
     * @return float
     */
    public function min() : float
    {
        return (float) min($this->a);
    }

    /**
     * Return the maximum element in the vector.
     *
     * @return float
     */
    public function max() : float
    {
        return (float) max($this->a);
    }

    /**
     * Return the mean of the vector.
     *
     * @return float
     */
    public function mean() : float
    {
        return array_sum($this->a) / $this->n;
    }

    /**
     * Return the median of the vector.
     *
     * @throws \RuntimeException
     * @return float
     */
    public function median() : float
    {
        $n = $this->size();

        if ($n < 1) {
            throw new RuntimeException('Median is only defined for vectors with'
                . ' 1 or more elements.');
        }

        $mid = intdiv($n, 2);

        $a = $this->a;

        sort($a);

        if ($n % 2 === 1) {
            $median = $a[$mid];
        } else {
            $median = ($a[$mid - 1] + $a[$mid]) / 2.;
        }

        return $median;
    }

    /**
     * Return the variance and the mean of the vector in a tuple.
     *
     * @return array
     */
    public function variance() : array
    {
        $mean = $this->mean();

        $variance = $this->subtractScalar($mean)->square()->sum();

        return [$variance, $mean];
    }

    /**
     * Round the elements in the matrix to a given decimal place.
     *
     * @param  int  $precision
     * @return self
     */
    public function round(int $precision = 0) : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = round($value, $precision);
        }

        return new self($b, false);
    }

    /**
     * Round the elements in the vector down to the nearest integer.
     *
     * @return self
     */
    public function floor() : self
    {
        return new self(array_map('floor', $this->a), false);
    }

    /**
     * Round the elements in the vector up to the nearest integer.
     *
     * @return self
     */
    public function ceil() : self
    {
        return new self(array_map('ceil', $this->a), false);
    }

    /**
     * Calculate the L1 or Manhattan norm of the vector.
     *
     * @return float
     */
    public function l1Norm() : float
    {
        return (float) array_sum(array_map('abs', $this->a));
    }

    /**
     * Calculate the L2 or Euclidean norm of the vector.
     *
     * @return float
     */
    public function l2Norm() : float
    {
        $norm = 0.;

        foreach ($this->a as $value) {
            $norm += $value ** 2;
        }

        return sqrt($norm);
    }

    /**
     * Calculate the max norm of the vector.
     *
     * @return float
     */
    public function maxNorm() : float
    {
        return (float) max(array_map('abs', $this->a));
    }

    /**
     * Clip the elements in the vector to be between given minimum and maximum
     * and return a new vector.
     *
     * @param  float  $min
     * @param  float  $max
     * @return self
     */
    public function clip(float $min, float $max) : self
    {
        $b = [];

        foreach ($this->a as $value) {
            if ($value > $max) {
                $b[] = $max;

                continue 1;
            }

            if ($value < $min) {
                $b[] = $min;

                continue 1;
            }

            $b[] = $value;
        }

        return new self($b, false);
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->n;
    }

    /**
     * @param  mixed  $index
     * @param  array  $values
     * @throws \RuntimeException
     * @return void
     */
    public function offsetSet($index, $values) : void
    {
        throw new RuntimeException('Vector cannot be mutated directly.');
    }

    /**
     * Does a given column exist in the matrix.
     *
     * @param  mixed  $index
     * @return bool
     */
    public function offsetExists($index) : bool
    {
        return isset($this->a[$index]);
    }

    /**
     * @param  mixed  $index
     * @throws \RuntimeException
     * @return void
     */
    public function offsetUnset($index) : void
    {
        throw new RuntimeException('Vector cannot be mutated directly.');
    }

    /**
     * Return a row from the matrix at the given index.
     *
     * @param  mixed  $index
     * @throws \InvalidArgumentException
     * @return int|float
     */
    public function offsetGet($index)
    {
        if (!isset($this->a[$index])) {
            throw new InvalidArgumentException('Element not found at index'
                . (string) $index . '.');
        }

        return $this->a[$index];
    }

    /**
     * Get an iterator for the rows in the matrix.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->a);
    }
}
