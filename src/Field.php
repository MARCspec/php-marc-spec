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
* A MARCspec fieldspec class
*/
class Field extends PositionOrRange implements FieldInterface, \JsonSerializable, \ArrayAccess
{

    /**
     * @var string field tag
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
    * {@inheritdoc}
    *
    * @throws InvalidMARCspecException
    */
    public function __construct($fieldspec)
    {        
        $this->checkIfString($fieldspec);
        
        $spec = trim($fieldspec);
        
        $specLength = strlen($fieldspec);
        
        // check string length
        if(3 > $specLength)
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::MINIMUM3,
                $fieldspec
            );
        }
        if(preg_match('/\s/', $fieldspec))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::SPACE,
                $fieldspec
            );
        }
        if($strpos = strpos($fieldspec, '{'))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::DETECTEDSS,
                $fieldspec
            );
        }
        
        $parser = new MARCspecParser();
        
        $parser->parse($fieldspec);
        
        if(array_key_exists('subfields',$parser->parsed))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::DETECTEDSF,
                $fieldspec
            );
        }

        if(array_key_exists('indicatorpos',$parser->parsed))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::DETECTEDIN,
                $fieldspec
            );
        }
       
        if($this->validateTag($parser->parsed['tag'])) $this->tag = $parser->parsed['tag'];
        
        if(array_key_exists('index',$parser->parsed))
        {
            $_pos = MARCspec::validatePos($parser->parsed['index']);
            
            $this->setIndexStartEnd($_pos[0],$_pos[1]);
        }
        else
        {
            // as of MARCspec 3.2.2 spec without index is always an abbreviation
            $this->setIndexStartEnd(0,"#");
        }
        
        if(array_key_exists('charpos',$parser->parsed))
        {
            $_chars = MARCspec::validatePos($parser->parsed['charpos']);
            
            $this->setCharStartEnd($_chars[0],$_chars[1]);
        }
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
    * validate a field tag
    *
    * @internal
    *
    * @access private
    *
    * @param string $tag The MARC spec as string field tag
    *
    * @throws InvalidMARCspecException
    *
    * @return true if string is a valid field tag
    */
    private function validateTag($tag)
    {
        if(!preg_match('/[.0-9a-z]{3,3}|[.0-9A-Z]{3,3}/', $tag))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::FTAG,
                $tag
            );
        }
        return true;
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
        $_fieldSpec['tag'] = $this->getTag();

        $_fieldSpec['indexStart'] = $this->getIndexStart();

        $_fieldSpec['indexEnd'] = $this->getIndexEnd();

        if(($indexLength = $this->getIndexLength()) !== null)
        {
            $_fieldSpec['indexLength'] = $indexLength;
        }

        if(($charStart = $this->getCharStart()) !== null)
        {
            $_fieldSpec['charStart'] = $charStart;
            $_fieldSpec['charEnd'] = $this->getCharEnd();
        }

        if(($charLength = $this->getCharLength()) !== null)
        {
            $_fieldSpec['charLength'] = $charLength;
        }

        if(($subSpecs = $this->getSubSpecs()) !== null)
        {
            $_fieldSpec['subSpecs'] = [];
            foreach($subSpecs as $key => $subSpec)
            {
                if(is_array($subSpec))
                {
                    foreach($subSpec as $altSubSpec)
                    {
                        $_fieldSpec['subSpecs'][$key][] = $altSubSpec->jsonSerialize();
                    }

                }
                else
                {
                    $_fieldSpec['subSpecs'][$key] = $subSpec->jsonSerialize();
                }
            }
        }
        return $_fieldSpec;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseSpec()
    {
        $fieldSpec = $this->getTag();
        
        $indexStart = $this->getIndexStart();
        $indexEnd = $this->getIndexEnd();

        $fieldSpec .= "[".$indexStart;
       
        if($indexStart !== $indexEnd)
        {
            $fieldSpec .= "-".$indexEnd;
        }
        $fieldSpec .= "]";
        
        if(($charStart = $this->getCharStart()) !== null)
        {
            $charEnd = $this->getCharEnd();
            if($charStart === 0 && $charEnd === "#")
            {
                // use abbreviation
            }
            else
            {
                $fieldSpec .= "/".$charStart;
                if($charEnd !== $charStart)
                {
                    $fieldSpec .= "-".$charEnd;
                }
            }
        }
        
        return $fieldSpec;
    }

    /**
    * {@inheritdoc}
    */
    public function __toString()
    {
        $fieldSpec = $this->getBaseSpec();

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
                    $fieldSpec .= '{'.implode('|',$subSpec).'}';
                }
                else
                {
                    $fieldSpec .= '{'.$subSpec->__toString().'}';
                }
            }
        }
        return $fieldSpec;
    }

    /**
     * Access object like an associative array
     *
     * @api
     *
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength
     * |indicator1|indicator2|subSpecs
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
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength
     * |indicator1|indicator2|subSpecs
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
            default: throw new \UnexpectedValueException("Offset $offset does not exist.");
        }
    }

    /**
     * Access object like an associative array
     *
     * @api
     *
     * @param string $offset Key indexStart|indexEnd|charStart|charEnd|charLength
     * |indicator1|indicator2|subSpecs
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
} // EOC
