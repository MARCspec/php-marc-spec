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

use CK\MARCspec\Exception\InvalidMARCspecException;

/**
* A MARCspec subspec class
*/
class SubSpec implements SubSpecInterface, \JsonSerializable, \ArrayAccess
{

    /**
     * @var string Operator
     */ 
    private $operator;
    
    /**
     * @var MARCspecInterface|ComparisonStringInterface The left hand subterm
     */ 
    private $leftSubTerm;
    
    /**
     * @var MARCspecInterface|ComparisonStringInterface The right hand subterm
     */ 
    private $rightSubTerm;
    
    /**
     * {@inheritdoc}
     * 
     * @throws \InvalidArgumentException
     * @throws InvalidMARCspecException
     */
    public function __construct($leftSubTerm, $operator, $rightSubTerm)
    {

        if($leftSubTerm instanceOf MARCspecInterface 
            || $leftSubTerm instanceOf ComparisonStringInterface)
        {
            $this->leftSubTerm = $leftSubTerm;
        }
        else
        {
            throw new \InvalidArgumentException(
                'Argument 1 must be instance of CK\MARCspec\MARCspecInterface or 
                CK\MARCspec\ComparisonStringInterface'
            );
        }
        
        if($rightSubTerm instanceOf MARCspecInterface 
            || $rightSubTerm instanceOf ComparisonStringInterface)
        {
            $this->rightSubTerm = $rightSubTerm;
        }
        else
        {
            throw new \InvalidArgumentException(
                'Argument 3 must be instance of CK\MARCspec\MARCspecInterface or 
                CK\MARCspec\ComparisonStringInterface. Got '
                .gettype($rightSubTerm)
            );
        }
        
        $this->setOperator($operator);
    }
    
    /**
     * Set operator
     * 
     * @throws InvalidMARCspecException
     */
    private function setOperator($operator)
    {
        if(!in_array($operator,["=", "!=", "~", "!~", "!", "?"],true))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SS.
                InvalidMARCspecException::OPERATOR,
                $operator
            );
        }
        $this->operator = $operator;

    }
    
    /**
     * {@inheritdoc}
     */
    public function getOperator()
    {
        return $this->operator;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLeftSubTerm()
    {
        return (isset($this->leftSubTerm)) ? $this->leftSubTerm : null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getRightSubTerm()
    {
        return $this->rightSubTerm;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "$this->leftSubTerm".$this->operator."$this->rightSubTerm";
    }
    
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        if(!is_null($this->leftSubTerm))
        {
            $_subSpec['leftSubTerm'] = $this->leftSubTerm->jsonSerialize();
        }
        $_subSpec['operator'] = $this->operator;
        $_subSpec['rightSubTerm'] = $this->rightSubTerm->jsonSerialize();
        return $_subSpec;
    }

    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key operator|leftSubTerm|rightSubTerm
     */ 
    public function offsetExists($offset)
    {
        switch($offset)
        {
            case 'operator': 
            case 'leftSubTerm':
            case 'rightSubTerm': return true;
                break;
            
            default: return false;
        }
    }
    
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key operator|leftSubTerm|rightSubTerm
     */ 
    public function offsetGet($offset)
    {
        switch($offset)
        {
            case 'operator': return $this->getOperator();
                break;
            
            case 'leftSubTerm': return $this->getLeftSubTerm();
                break;
            
            case 'rightSubTerm': return $this->getRightSubTerm();
                break;
            
            default: throw new \UnexpectedValueException("Offset $offset does not exist.");
        }
    }
    
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset
     */ 
    public function offsetSet($offset,$value)
    {
        throw new \UnexpectedValueException("Offset $offset cannot be set.");
    }
    
    /**
     * Access object like an associative array
     * 
     * @param string $offset
     */ 
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("Offset $offset can not be unset.");
    }
} // EOC
