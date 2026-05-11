<?php

/**
 * MARCspec is the specification of a reference, encoded as string, to a set of data
 * from within a MARC record.
 *
 * @author Carsten Klee <mailme.klee@yahoo.de>
 * @copyright For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CK\MARCspec;

class SpecIterator implements \Iterator
{
    private $var = [];

    public function __construct($array)
    {
        if (is_array($array)) {
            $this->var = $array;
        }
    }

    public function rewind(): void
    {
        reset($this->var);
    }

    public function current(): mixed
    {
        return current($this->var);
    }

    public function key(): mixed
    {
        return key($this->var);
    }

    public function next(): void
    {
        next($this->var);
    }

    public function valid(): bool
    {
        return key($this->var) !== null;
    }
}
