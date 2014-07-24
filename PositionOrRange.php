<?php
/**
* MARCspec is the specification of a reference, encoded as string, to a set of data from within a MARC record.
* 
* @author Carsten Klee <mailme.klee@yahoo.de>
* @package CK\MARCspec
* @copyright For the full copyright and license information, please view the LICENSE 
* file that was distributed with this source code.
*/
namespace CK\MARCspec;

use CK\MARCspec\Exception\InvalidMARCspecException;

/**
* class for index or character position or range spec
*/
class PositionOrRange implements PositionOrRangeInterface {

    /**
     * {@inheritdoc}
     */
    public function setIndexStartEnd($start,$end = null)
    {
        list($this->indexStart,$this->indexEnd) = $this->validateStartEnd($start,$end);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setIndexStartLength($start,$length)
    {
        list($this->indexStart,$this->indexEnd) = $this->validateStartLength($start,$length);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIndexStart()
    {
        return $this->getStartEnd($this->indexStart);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIndexEnd()
    {
        return $this->getStartEnd($this->indexEnd);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setCharStartEnd($start,$end = null)
    {
        list($this->charStart,$this->charEnd) = $this->validateStartEnd($start,$end);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setCharStartLength($start,$length)
    {
        list($this->charStart,$this->charEnd) = $this->validateStartLength($start,$length);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCharStart()
    {
        return $this->getStartEnd($this->charStart);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCharEnd()
    {
        return $this->getStartEnd($this->charEnd);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCharLength()
    {
        if( is_null($this->getCharStart()) && is_null($this->getCharEnd()) )
        {
            return null;
        }
        if( !is_null($this->getCharStart()) && is_null($this->getCharEnd()) )
        {
            return 1;
        }
        // both defined
        if($this->charStart === $this->charEnd) 
        {
            return 1;
        }
        if('#' === $this->charStart && '#' !== $this->charEnd)
        {
            return $this->charEnd + 1;
        }
        if('#' !== $this->charStart && '#' === $this->charEnd)
        {
            return null;
        }
        else
        {
            $length = $this->charEnd - $this->charStart + 1;
            if(1 > $length)
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::PR.
                    InvalidMARCspecException::NEGATIVE
                );
            }
            return $length;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getStartEnd($arg)
    {
        return (isset($arg)) ? $arg : null;
    }
    
    /**
    * validate a position or range
    * 
    * @access protected
    * 
    * @param string $pos The position or range
    * 
    * @throws InvalidMARCspecException
    * 
    * @return array $_pos[string] An numeric array of character or index positions. $_pos[1] might be empty.
    */
    protected function validatePos($pos)
    {
        $posLength = strlen($pos);
        
        if(1 > $posLength)
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR1,
                $pos
            );
        }
        
        for($x = 0; $x < $posLength; $x++)
        {
            if(!preg_match('/[0-9-#]/', $pos[$x]))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::PR.
                    InvalidMARCspecException::PR2,
                    $pos
                );
            }
        }
        
        if(strpos($pos,'-') === $posLength-1) // something like 123- is not valid
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR3,
                $pos
            );
        }
        
        if(0 === strpos($pos,'-'))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR4,
                $pos
            );
        }
        
        if(strpos($pos,'-') !== strrpos($pos,'-'))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR5,
                $pos
            );
        }
        
        $_pos = explode('-',$pos);
        
        if(2 < count($_pos))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR6,
                $pos
            );
        }
        
        if(1 == count($_pos))
        {
            $_pos[1] = '';
        }
        return $_pos;
    }

    /**
    *
    * Validate starting and ending position
    * 
    * @internal
    * 
    * @access private
    * 
    * @param int|string $start The starting position
    * @param int|string $end The ending position
    * 
    * @return null|array $_startEnd index 0 => start, index 1 => end
    * 
    * @throws \UnexpectedValueException
    */
    private function validateStartEnd($start,$end)
    {
        $_startEnd = array();
        
        if(preg_match('/[0-9]/', (string)$start))
        {
            $_startEnd[0] = (int)$start;
        }
        elseif('#' === $start)
        {
            $_startEnd[0] = '#';
        }
        else
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::PR.
                InvalidMARCspecException::PR7,
                $start
            );
        }
        
        if(!empty($end))
        {
            if('#' === $end)
            {
                $_startEnd[1] = '#';
            }
            elseif(preg_match('/[0-9]/', (string)$end))
            {
                $_startEnd[1] = (int)$end;
                
                if($_startEnd[1] < $_startEnd[0])
                {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::PR.
                        InvalidMARCspecException::PR8,
                        $start.'-'.$end
                    );
                }
            }
            else
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::PR.
                    InvalidMARCspecException::PR8,
                    $start.'-'.$end
                );
            }
        }
        else
        {
            $_startEnd[1] = null;
        }
        return $_startEnd;
    }
    
    /**
    *
    * Validate starting position and length
    * 
    * @internal
    * 
    * @access private
    * 
    * @param string $start The starting position
    * @param string $length $length The length count
    * 
    * @return array $_startEnd index 0 => start, index 1 => end
    * 
    * @throws \UnexpectedValueException
    */
    private function validateStartLength($start,$length)
    {

        $_startEnd = array();
        if(preg_match('/[0-9]/', (string)$start))
        {
            $_startEnd[0] = (int)$start;
        }
        elseif('#' === $start)
        {
            $_startEnd[0] = '#';
        }
        else
        {
            throw new \UnexpectedValueException(
                'First argument must be positive int, 0 or character #.',
                $start
            );
        }
        
        
        if(preg_match('/^[1-9]\d*/', (string)$length)) // only positive int without 0
        {
            if('#' !== $_startEnd[0])
            {
                $_startEnd[1] = (int)$length;
            }
            else
            {
                $_startEnd[1] = $_startEnd[0] + (int)$length -1;
            }
        }
        else
        {
            throw new \UnexpectedValueException(
                'Second argument must be positive int without 0.',
                $length
            );
        }
        return $_startEnd;
    }
    
    /**
     * checks if argument is a string
     * 
     * @internal
     * 
     * @access private
     * 
     * @param string $arg The argument to check
     * 
     * @throws \InvalidArgumentException if the argument is not a string
     */
    protected function checkIfString($arg)
    {
        if(!is_string($arg)) throw new \InvalidArgumentException("Method only accepts string as argument. " .gettype($arg)." given.");
    }
} // EOC
