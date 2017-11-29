<?php
/**
* MARCspec is the specification of a reference, encoded as string, to a set of data 
* from within a MARC record.
* 
* @author Carsten Klee <mailme.klee@yahoo.de>
* @package CK\MARCspec
* @copyright For the full copyright and license information, please view the LICENSE 
* file that was distributed with this source code.
*/
namespace CK\MARCspec;

class SpecIterator implements \Iterator
{
    private $var = array();

    public function __construct($array)
    {
        if (is_array($array))
        {
            $this->var = $array;
        }
    }

    public function rewind()
    {
        reset($this->var);
    }

    public function current()
    {
        $var = current($this->var);
        return $var;
    }

    public function key()
    {
        $var = key($this->var);
        return $var;
    }

    public function next()
    {
        $var = next($this->var);
        return $var;
    }

    public function valid()
    {
        $var = $this->current() !== false;
        return $var;
    }
}
