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
     * @var string indicator 1
     */
    private $indicator1;

    /**
     * @var string indicator 2
     */
    private $indicator2;

    /**
     * @var array subSpec
     */
    private $subSpecs = array();


    /**
    * {@inheritdoc}
    *
    * @throws InvalidMARCspecException
    */
    public function __construct($fieldspec = null)
    {
        if(is_null($fieldspec)) return;
        
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
        if($strpos = strpos('{', $fieldspec))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::DETECTEDSS,
                $fieldspec
            );
        }
        
        $parser = new MARCspecParser();
        
        $parser->fieldToArray($fieldspec);
        
        if(array_key_exists('subfields',$parser->parsed))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::DETECTEDSF,
                $fieldspec
            );
        }
       
        $this->setTag($parser->field['tag']);
        
        if(array_key_exists('index',$parser->field))
        {
            $_pos = MARCspec::validatePos($parser->field['index']);
            
            $this->setIndexStartEnd($_pos[0],$_pos[1]);
        }
        else
        {
            // as of MARCspec 3.2.2 spec without index is always an abbreviation
            $this->setIndexStartEnd(0,"#");
        }
        
        if(array_key_exists('indicators',$parser->field))
        {
            $this->setIndicators($parser->field['indicators']);
        }
        elseif(array_key_exists('charpos',$parser->field))
        {
            $_chars = MARCspec::validatePos($parser->field['charpos']);
            
            $this->setCharStartEnd($_chars[0],$_chars[1]);
        }
    }

    /**
    *
    * {@inheritdoc}
    */
    public function setTag($arg)
    {
        if($this->validateTag($arg)) $this->tag = $arg;
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
    *
    * {@inheritdoc}
    */
    public function setIndicators($arg)
    {
        $this->checkIfString($arg);
        if($this->validateIndicators($arg))
        {
            for($x = 0; $x < strlen($arg); $x++)
            {
                if(0 == $x)
                {
                    if('_' != $arg[$x]) $this->setIndicator1($arg[$x]);
                }
                if(1 == $x)
                {
                    if('_' != $arg[$x]) $this->setIndicator2($arg[$x]);
                }
            }
        }
    }

    /**
    *
    * {@inheritdoc}
    */
    public function setIndicator1($arg)
    {
        if($this->validateIndicators($arg)) $this->indicator1 = $arg;
    }

    /**
    *
    * {@inheritdoc}
    */
    public function getIndicator1()
    {
        return (isset($this->indicator1)) ? $this->indicator1 : null;
    }

    /**
    *
    * {@inheritdoc}
    */
    public function setIndicator2($arg)
    {
        if($this->validateIndicators($arg)) $this->indicator2 = $arg;
    }

    /**
    *
    * {@inheritdoc}
    */
    public function getIndicator2()
    {
        return (isset($this->indicator2)) ? $this->indicator2 : null;
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
     * validate indicators
     *
     * @internal
     *
     * @access private
     *
     * @param string $indicators The MARC spec as string indicators
     *
     * @throws InvalidMARCspecException
     *
     * @return true if $indicators is a valid indicators spec
     */
    private function validateIndicators($indicators)
    {
        $indLength = strlen($indicators);
        if(2 < $indLength)
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::LENGTHIND,
                $indicators
            );
        }
        
        if(preg_match('/[^a-z0-9_]/', $indicators))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::INDCHAR2,
                $indicators
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

        if(($indicator1 = $this->getIndicator1()) !== null)
        {
            $_fieldSpec['indicator1'] = $indicator1;
        }

        if(($indicator2 = $this->getIndicator2()) !== null)
        {
            $_fieldSpec['indicator2'] = $indicator2;
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
        
        $indicator1 = ($this->getIndicator1() !== null) ? $this->indicator1 : "_";
        $indicator2 = ($this->getIndicator2() !== null) ? $this->indicator2 : "_";
        $indicators = $indicator1.$indicator2;
        if($indicators != "__") $fieldSpec .= "_".$indicators;
        
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
            case 'indicator1': return isset($this->indicator1);
            break;
            case 'indicator2': return isset($this->indicator2);
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
            case 'indicator1': return $this->getIndicator1();
            break;
            case 'indicator2': return $this->getIndicator2();
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

            case 'indicator1': $this->setIndicator1($value);
            break;

            case 'indicator2': $this->setIndicator2($value);
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
