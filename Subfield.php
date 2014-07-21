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
* A MARCspec subfield class
*/
class Subfield extends PositionOrRange implements SubfieldInterface, \JsonSerializable, \ArrayAccess {

    /**
     * @var string subfield tag
     */
    private $tag;
    
    /**
     * @var int|string indexStart
     */
    protected $indexStart;
    
    /**
     * @var int|string indexEnd
     */
    protected $indexEnd;
    
    /**
     * @var int|string starting position
     */
    protected $charStart;
    
    /**
     * @var int|string ending position
     */
    protected $charEnd;
    
    /**
     * @var array subSpec
     */
    private $subSpecs = array();

    /**
    *
    * {@inheritdoc}
    * 
    * @throws \InvalidArgumentException
    * @throws InvalidMARCspecException
    * 
    */
    public function __construct($subfieldspec)
    {
        if(!is_string($subfieldspec))
        {
            throw new \InvalidArgumentException("Method only accepts string as argument. " .gettype($subfieldspec)." given.");
        }
        if('$' !== $subfieldspec[0])
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::PREFIX,
                $subfieldspec
            );
        }
        $this->validateSubfield(substr($subfieldspec,1));
    }
    


    /**
    *
    * {@inheritdoc}
    * 
    */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * validates and sets subfields
     *
     * @internal
     * 
     * @access private
     * 
     * @param string $arg The subfield tag
     * 
     * @throws InvalidMARCspecException
     */
    private function validateSubfield($arg)
    {
        if(empty($arg))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::MISSINGTAG,
                $arg
            );
        }
        
        $argLength = strlen($arg);
        
        for($i = 0;$i<$argLength;$i++)
        {
            if(!preg_match('/[!\"#$%&\'()*+,-.\/0-9:;<=>?[\]^_`a-z{}~]/', $arg[$i]))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidMARCspecException::SFCHAR,
                    $arg
                );
            }
        }
        
        if(1<$argLength) 
        {
            if('-' == $arg[1]) // assuming subfield range
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidMARCspecException::SFRANGE,
                    $arg
                );
            }
        }
        
        if(preg_match('/(?<!\$)\{/', $arg))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::DETECTEDSS,
                $arg
            );
        }

        if($argLength > 1) // assuming index, subfield range or character position or range
        {
            $_split = preg_split('/\[(.*)\]/',$arg,-1,PREG_SPLIT_DELIM_CAPTURE);
            if(3 == count($_split)) // assuming index and character position or range
            {
                if($argLength < 4)
                {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::SF.
                        InvalidMARCspecException::MINIMUM4,
                        $arg
                    );
                }
                
                $this->tag  = $arg[0];
                
                $_index = $this->validatePos($_split[1]);
                
                $this->setIndexStartEnd($_index[0],$_index[1]);
                
                if(!empty($_split[2])) // assuming character position or range
                {
                    
                    $this->setCharPos($_split[2]);
                }
                
            }
            elseif(1 == count($_split) && '/' == $arg[1]) // assuming character position or range
            {
                $this->tag  = $arg[0];
                $this->setCharPos(substr($arg,1));
            }
            else
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidMARCspecException::UNKNOWN,
                    $arg
                );
            }
        }
        else // simple subfield
        {
            $this->tag = $arg;
        }
    }
    /**
     * set subfield character position or range
     * 
     * @internal
     * 
     * @access private
     * 
     * @param string $subfield A subfield tag
     * 
     * @param string $charposSpec A character position or range spec
     */
    private function setCharPos($charposSpec)
    {
        
        if('/' != $charposSpec[0]) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::MISSINGSLASH,
                $charposSpec
            );
        }
        if(strlen($charposSpec) < 2) 
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::MINIMUM2,
                $charposSpec
            );
        }
        $charPos = substr($charposSpec,1);
        $_charPos = $this->validatePos($charPos);

        $this->setCharStartEnd($_charPos[0],$_charPos[1]);
    }
    
    /**
    *
    * {@inheritdoc}
    */
    public function addSubSpec($SubSpec)
    {
        if($SubSpec instanceOf SubSpecInterface)
        {
            $this->subSpecs[] = $SubSpec;
        }
        elseif(is_array($SubSpec))
        {
            foreach($SubSpec as $sub)
            {
                if( !($sub instanceOf SubSpecInterface) )
                {
                    throw new \InvalidArgumentException('Values of array of subSpecs must be instances of SubSpecInterface.');
                }
            }
            $this->subSpecs[] = $SubSpec;
        }
        else
        {
            throw new \InvalidArgumentException('Param 1 must be instance of SubSpecInterface or array with instances of SubSpecInterface. Got "'.gettype($subSpec).'".');
        }
    }
    
    /**
    *
    * {@inheritdoc}
    */
    public function getSubSpecs()
    {
        return (0 < count($this->subSpecs)) ? $this->subSpecs : null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $_subfieldSpec['tag'] = $this->getTag();
        if(($indexStart = $this->getIndexStart()) !== null) $_subfieldSpec['indexStart'] = $indexStart;
        if(($indexEnd = $this->getIndexEnd()) !== null) $_subfieldSpec['indexEnd'] = $indexEnd;
        if(($charStart = $this->getCharStart()) !== null) $_subfieldSpec['charStart'] = $charStart;
        if(($charEnd = $this->getCharEnd()) !== null) $_subfieldSpec['charEnd'] = $charEnd;
        if(($charLength = $this->getCharLength()) !== null) $_subfieldSpec['charLength'] = $charLength;
        if(($subSpecs = $this->getSubSpecs()) !== null)
        {
            $_subfieldSpec['subSpecs'] = [];
            foreach($subSpecs as $key => $subSpec)
            {
                if(is_array($subSpec))
                {
                    foreach($subSpec as $altSubSpec)
                    {
                        $_subfieldSpec['subSpecs'][$key][] = $altSubSpec->jsonSerialize();
                    }
                    
                }
                else
                {
                    $_subfieldSpec['subSpecs'][$key] = $subSpec->jsonSerialize();
                }
            }
        }
        return $_subfieldSpec;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBaseSpec()
    {
        $subfieldSpec = '$'.$this->getTag();
        if(($indexStart = $this->getIndexStart()) !== null)
        {
            $subfieldSpec .= "[".$indexStart;
            if(($indexEnd = $this->getIndexEnd()) !== null) $subfieldSpec .= "-".$indexEnd;
            $subfieldSpec .= "]";
        }
        if(($charStart = $this->getCharStart()) !== null)
        {
            $subfieldSpec .= "/".$charStart;
            if(($charEnd = $this->getCharEnd()) !== null) $subfieldSpec .= "-".$charEnd;
        }
        return $subfieldSpec;
    }
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $subfieldSpec = $this->getBaseSpec();
        
        if(($subSpecs = $this->getSubSpecs()) !== null)
        {
            foreach($subSpecs as $subSpec)
            {
                if(is_array($subSpec))
                {
                    foreach($subSpec as $orKey => $orSubSpec)
                    {
                        $subSpec[$orKey] = $orSubSpec->__toString();
                    }
                    $subfieldSpec .= '{'.implode('|',$subSpec).'}';
                }
                else
                {
                    $subfieldSpec .= '{'.$subSpec->__toString().'}';
                }
            }
        }
        return $subfieldSpec;
    }
    
    
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength|subSpecs
     */ 
    public function offsetExists($offset)
    {
        switch($offset)
        {
            case 'tag': return isset($this->tag);
            break;
            case 'indexStart': return isset($this->indexStart);
            break;
            case 'indexEnd': return isset($this->indexEnd);
            break;
            case 'charStart': return isset($this->charStart);
            break;
            case 'charEnd': return isset($this->charEnd);
            break;
            case 'subSpecs': return (0 < count($this->subSpecs)) ? true : false;
            break;
            default: return false;
        }
    }
    
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength|subSpecs
     */ 
    public function offsetGet($offset)
    {
        switch($offset)
        {
            case 'tag': return $this->getTag();
            break;
            case 'indexStart': return $this->getIndexStart();
            break;
            case 'indexEnd': return $this->getIndexEnd();
            break;
            case 'charStart': return $this->getCharStart();
            break;
            case 'charEnd': return $this->getCharEnd();
            break;
            case 'charLength': return $this->getCharLength();
            break;
            case 'subSpecs': return $this->getSubSpecs();
            break;
            default: return null;
        }
    }
    
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength|subSpecs
     */ 
    public function offsetSet($offset,$value)
    {
        switch($offset)
        {
            case 'indexStart': $this->setIndexStartEnd($value);
            break;
            case 'indexEnd':
            if(!isset($this['indexStart']))
            {
                $this->setIndexStartEnd($value,$value);
            }
            else
            {
                $this->setIndexStartEnd($this['indexStart'],$value);
            }
            break;
            case 'charStart': $this->setCharStartEnd($value);
            break;
            case 'charEnd':
            if(!isset($this['charStart']))
            {
                $this->setCharStartEnd($value,$value);
            }
            else
            {
                $this->setCharStartEnd($this['charStart'],$value);
            }
            break;
            case 'charLength': throw new \UnexpectedValueException("CharLength is always calculated.");
            break;
            case 'subSpecs': $this->addSubSpec($value);
            break;
            default: throw new \UnexpectedValueException("Offset $offset cannot be set.");
        }
    }
    
    /**
     * Access object like an associative array
     */ 
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("Offset $offset can not be unset.");
    }
} // EOC
