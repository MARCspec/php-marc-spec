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
* A MARCspec comparison string class
*/
class ComparisonString implements ComparisonStringInterface, \JsonSerializable, \ArrayAccess
{

    /**
     * @var string The escaped comparison string
     */ 
    private $raw;

    /**
    *
    * {@inheritdoc}
    * 
    * @throws \InvalidArgumentException if argument is not a string or 
    * comparison string is not properly escaped
    */
    public function __construct($raw)
    {
        
        if(!is_string($raw))
        {
            throw new \InvalidArgumentException('Argument must be of type string. Got '
                .gettype($raw).'.'
            );
        }
        
        if(false !== strpos($raw,' '))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::CS.
                InvalidMARCspecException::SPACE,
                $raw
            );
        }
        
        /** char of list ${}!=~?|\s must be escaped if not at index 0*/
        if(!preg_match('/^(.(?:[^${}!=~?| ]|(?<=\\\\)[${}!=~?|])*)$/',$raw))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::CS.
                InvalidMARCspecException::ESCAPE,
                $raw
            );
        }
        
        $this->raw = $raw;
    }
    
    /**
    * {@inheritdoc}
    */
    public function getComparable()
    {
        $comparable = str_replace('\s',' ',$this->raw);
        return stripcslashes($comparable);
    }
    
    /**
    * {@inheritdoc}
    */
    public function getRaw()
    {
        return $this->raw;
    }
    
    /**
    * {@inheritdoc}
    */
    public static function escape($arg)
    {
        $specialChars = ['{','}','!','=','~','?'];
        for($i = 0; $i < count($specialChars);$i++)
        {
            $arg = str_replace($specialChars[$i],'\\'.$specialChars[$i],$arg);
        }
        return $arg = str_replace(' ','\s',$arg);
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return "\\".$this->raw;
    }
    
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return ['comparisonString'=>$this->raw];
    }
    
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key raw|comparable
     */ 
    public function offsetExists($offset)
    {
        switch($offset)
        {
            case 'raw': 
            case 'comparable': return true;
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
            case 'raw': return $this->getRaw();
            break;
            case 'comparable': return $this->getComparable();
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
