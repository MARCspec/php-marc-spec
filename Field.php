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
* A MARCspec fieldspec class
*/
class Field extends PositionOrRange implements FieldInterface, \JsonSerializable {

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
        if($strpos = strpos('{', $fieldspec))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::DETECTEDSS,
                $fieldspec
            );
        }
        
        /**
         * $specMatches[0] => whole spec
         * $specMatches[1] => tag
         * $specMatches[2] => indexSpec
         * $specMatches[3] => charSpec
         * $specMatches[4] => indicatorSpec
         * $specMatches[5] => useless
         */
        if(0 === preg_match('/^(.{3,3})(\[.*\])?(\/[^_]*)?(_[^{]*)?(.*)?/',$fieldspec,$specMatches))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::UNKNOWN,
                $fieldspec
            );
        }
        
        $this->setTag($specMatches[1]); 
        
        if($specLength > 3)
        {
            // check an set indexSpec
            if(!array_key_exists(2,$specMatches))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::FS.
                    InvalidMARCspecException::INDEX,
                    $fieldspec
                );
            }
            
            if(!empty($specMatches[2]))
            {
                if( preg_match('/^\[(.*)\]/', $specMatches[2], $_assumedIndex) )
                {
                    if( $_index = $this->validatePos($_assumedIndex[1]) )
                    {
                        $indexEnd = null;
                        if(array_key_exists('1',$_index))
                        {
                            if(!empty($_index[1]))
                            {
                                $indexEnd = $_index[1];
                            }
                        }
                        $this->setIndexStartEnd($_index[0],$indexEnd);
                    }
                }
                else
                {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::FS.
                        InvalidMARCspecException::INDEX,
                        $fieldspec
                    );
                }
            }
            
            if(!empty($specMatches[3]))
            {
                // check character position or range
                $charPosOrRange = substr($specMatches[3], 1);
                if('' != $charPosOrRange)
                {
                    if($_charPosOrRange = $this->validatePos($charPosOrRange))
                    {
                        $charEnd = null;
                        if(array_key_exists(1,$_charPosOrRange))
                        {
                            if(!empty($_charPosOrRange[1]))
                            {
                                $charEnd = $_charPosOrRange[1];
                            }
                        }
                        $this->setCharStartEnd($_charPosOrRange[0],$charEnd);
                    }
                }
                else
                {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::PR.
                        InvalidMARCspecException::PRCHAR,
                        $fieldspec
                    );
                }
            }
            
            if(!empty($specMatches[4]))
            {
                $this->setIndicators(substr($specMatches[4],1));
            }
            
            if(!empty($specMatches[5]))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::FS.
                    InvalidMARCspecException::USELESS,
                    $fieldspec
                );
            }
        }
    }
    
    /**
    *
    * Set the field tag
    *
    * Provided param gets validated
    *
    * @access private
    * 
    * @param string $arg The field tag
    */
    private function setTag($arg)
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
        elseif(preg_match('/_{2,2}/', $indicators))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::INDCHAR1,
                $indicators
            );
        }
        for($x = 0; $x < strlen($indicators); $x++)
        {
            if(!preg_match('/[a-z0-9_]/', $indicators[$x]))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::FS.
                    InvalidMARCspecException::INDCHAR2,
                    $indicators
                );
            }
        }
        return true;
    }
    
    public function addSubSpec(SubSpecInterface $subSpec)
    {
        $this->subSpecs[] = $subSpec;
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
        if(($indexStart = $this->getIndexStart()) !== null) $_fieldSpec['indexStart'] = $indexStart;
        if(($indexEnd = $this->getIndexEnd()) !== null) $_fieldSpec['indexEnd'] = $indexEnd;
        if(($charStart = $this->getCharStart()) !== null) $_fieldSpec['charStart'] = $charStart;
        if(($charEnd = $this->getCharEnd()) !== null) $_fieldSpec['charEnd'] = $charEnd;
        if(($charLength = $this->getCharLength()) !== null) $_fieldSpec['charLength'] = $charLength;
        if(($indicator1 = $this->getIndicator1()) !== null) $_fieldSpec['indicator1'] = $indicator1;
        if(($indicator2 = $this->getIndicator2()) !== null) $_fieldSpec['indicator2'] = $indicator2;
        if(($subSpecs = $this->getSubSpecs()) !== null)
        {
            foreach($subSpecs as $subSpec) $_fieldSpec['subSpecs'][] = $subSpec->jsonSerialize();
        }
        return $_fieldSpec;
    }
    
    /**
    * {@inheritdoc}
    */
    public function __toString()
    {
        $fieldSpec = $this->getTag();
        if(($indexStart = $this->getIndexStart()) !== null)
        {
            $fieldSpec .= "[".$indexStart;
            if(($indexEnd = $this->getIndexEnd()) !== null) $fieldSpec .= "-".$indexEnd;
            $fieldSpec .= "]";
        }
        if(($charStart = $this->getCharStart()) !== null)
        {
            $fieldSpec .= "/".$charStart;
            if(($charEnd = $this->getCharEnd()) !== null) $fieldSpec .= "-".$charEnd;
        }
        $indicator1 = ($this->getIndicator1() !== null) ? $this->indicator1 : "_";
        $indicator2 = ($this->getIndicator2() !== null) ? $this->indicator2 : "_";
        $indicators = $indicator1.$indicator2;
        if($indicators != "__") $fieldSpec .= "_".$indicators;
        if(($subSpecs = $this->getSubSpecs()) !== null)
        {
            foreach($subSpecs as $subSpec) $fieldSpec .= $subSpecs->__toString();
        }
        return $fieldSpec;
    }
} // EOC
