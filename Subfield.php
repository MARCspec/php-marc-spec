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
* A MARCspec subfield class
*/
class Subfield extends PositionOrRange implements SubfieldInterface, \JsonSerializable, \ArrayAccess, \IteratorAggregate
{

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
    private $subSpecs = [];

    /**
    *
    * {@inheritdoc}
    * 
    * @throws \InvalidArgumentException
    * @throws InvalidMARCspecException
    * 
    */
    public function __construct($subfieldspec = null)
    {
        if(is_null($subfieldspec)) return;
        
        if(!is_string($subfieldspec))
        {
            throw new \InvalidArgumentException("Method only accepts string as argument. " .
            gettype($subfieldspec)." given."
            );
        }
        
        $argLength = strlen($subfieldspec);
        
        if(0 === $argLength)
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::MISSINGTAG,
                $subfieldspec
            );
        }
        
        if(1 == $argLength)
        {
            $subfieldspec = "$".$subfieldspec;
        }

        if(1<$argLength) 
        {
            if('-' == $subfieldspec[1]) // assuming subfield range
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidMARCspecException::SFRANGE,
                    $subfieldspec
                );
            }
        }
        
        if(preg_match('/\{.*\}$/', $subfieldspec))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::DETECTEDSS,
                $subfieldspec
            );
        }
        
        $parser = new MARCspecParser();
        
        $subfield = $parser->subfieldToArray($subfieldspec);
        
        $this->setTag($subfield['subfieldtag']);
        
        if(array_key_exists('index',$subfield))
        {
            $_pos = MARCspec::validatePos($subfield['index']);
            
            $this->setIndexStartEnd($_pos[0],$_pos[1]);
        }
        else
        {
            // as of MARCspec 3.2.2 spec without index is always an abbreviation
            $this->setIndexStartEnd(0,"#");
        }
        
        if(array_key_exists('charpos',$subfield))
        {
            $_chars = MARCspec::validatePos($subfield['charpos']);
            
            $this->setCharStartEnd($_chars[0],$_chars[1]);
        }
    }
    

    /**
    * {@inheritdoc}
    */
    public function setTag($arg)
    {
        if(!preg_match('/^[\!-\?\[-\{\}-~]$/',$arg))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::SFCHAR,
                $arg
            );
        }
        $this->tag = $arg;
    }

    /**
    * {@inheritdoc}
    */
    public function getTag()
    {
        return (isset($this->tag)) ? $this->tag : null;
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
                    throw new \InvalidArgumentException('Values of array of subSpecs 
                        must be instances of SubSpecInterface.'
                    );
                }
            }
            $this->subSpecs[] = $SubSpec;
        }
        else
        {
            throw new \InvalidArgumentException('Param 1 must be instance of 
                SubSpecInterface or array with instances of SubSpecInterface. Got "'
                .gettype($subSpec).'".'
            );
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
        
        $_subfieldSpec['indexStart'] =  $this->getIndexStart();
        
        $_subfieldSpec['indexEnd'] = $this->getIndexEnd();
        
        if(($indexLength = $this->getIndexLength()) !== null)
        {
            $_subfieldSpec['indexLength'] = $indexLength;
        }
        
        if(($charStart = $this->getCharStart()) !== null) 
        {
            $_subfieldSpec['charStart'] = $charStart;
            $_subfieldSpec['charEnd'] = $this->getCharEnd();
        }
        
        if(($charLength = $this->getCharLength()) !== null) 
        {
            $_subfieldSpec['charLength'] = $charLength;
        }
        
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

        $subfieldSpec .= "[".$this->getIndexStart()."-".$this->getIndexEnd()."]";

        if(($charStart = $this->getCharStart()) !== null)
        {
            $subfieldSpec .= "/".$charStart."-".$this->getCharEnd();
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
            case 'indexLength': return !is_null($this->getIndexLength());
            break;
            case 'charStart': return isset($this->charStart);
            break;
            case 'charEnd': return isset($this->charEnd);
            break;
            case 'charLength': return !is_null($this->getCharLength());
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
            case 'indexLength': return $this->getIndexLength();
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
            case 'tag': $this->setTag($value);
            break;
            
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
            
            case 'indexLength': throw new \UnexpectedValueException("indexLength is always calculated.");
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
    
    public function getIterator()
    {
        foreach($this as $key => $value)
        {
            $_[$key] = $value;
        }
        return new SpecIterator($_);
    }
} // EOC
