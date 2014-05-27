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

/**
* Class to decode, validate and encode MARC spec as string.
* For Specification of MARC spec as string see
* <http://cklee.github.io/marc-spec/marc-spec.html>
*/
class MARCspec implements \JsonSerializable{

    /**
    * @var string field tag
    */
    private $fieldTag;
    
    /**
    * @var int|string starting character position
    */
    private $charStart;
    
    /**
    * @var int|string ending character position
    */
    private $charEnd;
    
    /**
    * @var array Associative array of subfield tags as key and value
    */
    private $subfields = array();
    
    /**
    * @var array Associative array of subfield indizes
    */
    private $subfieldIndex = array();
    
    /**
    * @var string currently added subfield
    */
    private $currentSubfield;
    
    /**
    * @var string indicator 1
    */
    private $indicator1;
    
    /**
    * @var string indicator 2
    */
    private $indicator2;
    
    /**
    * @var int|string fieldIndexStart
    */
    private $fieldIndexStart;
    
    /**
    * @var int|string fieldIndexEnd
    */
    private $fieldIndexEnd;
    
    /**
    * @var array subSpec
    */
    private $subSpecs = array();
    
    
    /**
    * Constructor
    *
    * @uses MARCspec::validate() to parse and validate the spec
    * @param string $spec The spec to parse
    */
    public function __construct($spec = false)
    {
        if($spec) $this->validate($spec);
    }
    
   /**
   * parse a MARC spec as string into an object
   *
   * @api
   * @access public
   * @uses MARCspec::validate()    to parse validate the spec
   * @return bool true if spec is valid, false if not
   * @param string $spec The Marc spec as string
   */
    public function decode($spec)
    {
        return $this->validate($spec);
    }
    


    /**
    *
    * Set the field tag
    *
    * Provided param gets validated
    *
    * @api
    * @access public
    * @property-write string $fieldTag
    * @uses MARCspec::validateFieldTag()    to validate the field tag
    * @param string $arg The field tag
    */
    public function setFieldTag($arg)
    {
        if($this->validateFieldTag($arg)) $this->fieldTag = $arg;
    }
    
    /**
    *
    * Get the field tag
    *
    * @api
    * @access public
    * @return null|string MARCspec::$fieldTag The field tag
    */
    public function getFieldTag()
    {
        return (isset($this->fieldTag)) ? $this->fieldTag : null;
    }


    /**
     *
     * Set character starting and ending position
     *
     * @api
     * @access public
     * @uses MARCspec::validateStartEnd()    to validate starting and ending position
     * @property-write int|string $charStart
     * @property-write int|string $charEnd
     * @param int|string $start The character starting position
     * @param int|string|null $end The character ending position
     *
     */
    public function setCharStartEnd($start,$end = null)
    {
        $_startEnd = $this->validateStartEnd($start,$end);
        $this->charStart = $_startEnd[0];
        $this->charEnd = $_startEnd[1];
    }
    
    /**
     *
     * Set character starting and ending position via start and length
     * 
     * 
     * @api
     * @access public
     * @uses MARCspec::validateStartLength()     to validate starting and ending position 
     * @property-write int|string $charStart The character starting position
     * @property-write int|string $charEnd The character ending position
     * @param int|string $start The character starting position
     * @param int $length The character length count
     *
     */
    public function setCharStartLength($start,$length)
    {
        $_startEnd = $this->validateStartLength($start,$length);
        $this->charStart = $_startEnd[0];
        $this->charEnd = $_startEnd[1];
    }
    
    /**
    *
    * Get the character starting position
    *
    * @api
    * @access public
    * @return null|int MARCspec::$charStart The character starting position 
    */
    public function getCharStart()
    {
        return (isset($this->charStart)) ? $this->charStart : null;
    }
    

    /**
    *
    * Get the character ending position
    *
    * @api
    * @access public
    * @return null|int MARCspec::$charEnd The character ending position 
    */
    public function getCharEnd()
    {
        return (isset($this->charEnd)) ? $this->charEnd : null;
    }
    
    /**
    *
    * Get length of character range
    *
    * @api
    * @access public
    * @uses MARCspec::getCharStart()    to retrieve the character starting position
    * @uses MARCspec::getCharEnd()      to retrieve the character ending position
    * @return null|int $length The character length
    * @throws \InvalidArgumentException if length is less than 1
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
            if(1 > $length)   throw new \InvalidArgumentException('Ending character position or index must be equal or higher than starting character potion or index.');
            return $length;
        }
    }

    
    /**
    *
    * Add subfield tag
    *
    * Adds a subfield tag to the array of subfield tags
    *
    * @api
    * @access public
    * @param string $arg
    * @uses MARCspec::detectSubfields()     to detect subfields
    * @uses MARCspec::validateSubfield()    to validate and set the subfield
    * @throws \InvalidArgumentException if MARCspec::$charStart isset or field tag is 'LDR'
    */
    public function addSubfields($arg)
    {
        $this->checkFieldTag();
        $this->checkIfString($arg);
        if(isset($this->charStart) or 'LDR' === $this->fieldTag)
        {
            throw new \InvalidArgumentException('For leader or control fields subfield spec is not allowed.');
        }

        $_sf = $this->detectSubfields($sf_save);
        $rest = '';
        $sfCount = count($_sf);
        if(array_key_exists('subspec',$_sf))
        {
            $subspec = $_sf['subspec'];
            $sfCount -= 1; 
        }

        for($r = 0; $r < $sfCount;$r++)
        {
            if('|' == $_sf[$r])
            {
                $_sf[$r] = '$'; // undo save subfield tag $
            }
            $this->validateSubfield($_sf[$r]);
        }
        
        if(!empty($subspec))
        {
            do
            {
                $rest = $this->detectSubSpecs($subspec);
            }
            while('{' == $rest[0]);
            
            if(!empty($rest))
            {
                $this->addSubfields($rest); // call recursively
            }
        }
    }
    
    /**
    * detects subfields
    * breaks on subspecs
    *
    * @internal
    * @access private
    * @param string $arg string of subfields
    * @throws \InvalidArgumentException if characters not allowed are used* 
    * @return array $_sunfields Array of subfields and subspec plus rest
    */
    private function detectSubfields($arg)
    {
        $sf_save = str_replace('$$','$|',$arg); // save subfield tag $
        $_subfields = [];
        $marker = 0;
        for($x = 0; $x < strlen($sf_save); $x++)
        {
            if('{' == $sf_save[$x] && '$' != $sf_save[$x-1]) // assumed subspec
            {
                $_subfields['subspec'] = str_replace('|','$',substr($sf_save,$x));
                return $_subfields;
            }
            elseif('$' == $sf_save[$x])
            {
                if(0 < $x) $marker++;
            }
            else
            {
                $_subfields[$marker] .= str_replace('|','$',$sf_save[$x]);
                if(!preg_match('/[!\"#$%&\'()*+,-.\/0-9:;<=>?[\]^_`a-z{}~]/', $_subfields[$marker]))
                {
                    throw new \InvalidArgumentException('For subfields only digits, lowercase alphabetic characters or one of "!"#$%&\'()*+,-./0-9:;<=>?[\]^_`a-z{}~" are allowed. But "'.$_subfields[$marker].'" given in "'.$arg.'".');
                }
            }
        }
        return $_subfields;
    }
    
    /**
    *
    * Get subfield tags
    *
    * @api
    * @access public
    * @return null|array MARCspec::$subfields The array of subfields
    */
    public function getSubfields()
    {
        if(0 < count($this->subfields))
        {
            return $this->subfields;
        }
        else
        {
            return null;
        }
    }
    
    /**
    *
    * Set indicators
    *
    * @api
    * @access public
    * @uses MARCspec::validateIndicators()  to validate the indicators
    * @uses MARCspec::setIndicator1()  to set the indicator 1
    * @uses MARCspec::setIndicator2()  to set the indicator 2
    * @param string $arg
    */
    public function setIndicators($arg)
    {
        $this->checkIfString($arg);
        if($this->validateIndicators($arg))
        {
            for($x = 0; $x < strlen($arg); $x++)
            {
                if(0 === $x)
                {
                    if('_' != $arg[$x]) $this->setIndicator1($arg[$x]);
                }
                if(1 === $x)
                {
                    if('_' != $arg[$x]) $this->setIndicator2($arg[$x]);
                }
            }
        }
    }
    
    /**
    *
    * Set indicator 1
    *
    * @api
    * @access public
    * @uses MARCspec::validateIndicators() to validate the argument
    * @property-write string MARCspec::$indicator1 The indicator 1 
    * @param string $arg
    */
    public function setIndicator1($arg)
    {
        $this->checkFieldTag();
        if($this->validateIndicators($arg)) $this->indicator1 = $arg;
    }
    
    /**
    *
    * Get indicator 1
    *
    * @api
    * @access public
    * @return null|string MARCspec::$indicator1 The indicator 1
    */
    public function getIndicator1()
    {
        return (isset($this->indicator1)) ? $this->indicator1 : null;
    }
    
    /**
    *
    * Set indicator 2
    *
    * @api
    * @access public
    * @uses MARCspec::validateIndicators() to validate the argument
    * @property-write string MARCspec::$indicator1 The indicator 2 
    * @param string $arg
    */
    public function setIndicator2($arg)
    {
        $this->checkFieldTag();
        if($this->validateIndicators($arg)) $this->indicator2 = $arg;
    }
    
    /**
    *
    * Get indicator 2
    *
    * @api
    * @access public
    * @return null|string MARCspec::$indicator2 The indicator 2
    */
    public function getIndicator2()
    {
        return (isset($this->indicator2)) ? $this->indicator2 : null;
    }
    
    /**
    *
    * Set the field index starting and ending position
    *
    * @api
    * @access public
    * @uses MARCspec::validateStartEnd()        to validate start and end index
    * @property-write int|string MARCspec::$fieldIndexStart The field index starting position
    * @property-write int|string MARCspec::$fieldIndexEnd The field index ending position 
    * @param int|string $start The index starting position
    * @param int|string|null $end The index ending position
    */
    public function setFieldIndexStartEnd($start,$end = null)
    {
        $_startEnd = $this->validateStartEnd($start,$end);
        $this->fieldIndexStart = $_startEnd[0];
        $this->fieldIndexEnd = $_startEnd[1];
    }
    
    /**
    *
    * Set the field index starting and ending position via length
    *
    * @api
    * @access public
    * @uses MARCspec:validateStartLength()      to validate start index and length
    * @property-write int|string MARCspec::$fieldIndexStart The field index starting position
    * @property-write int|string MARCspec::$fieldIndexEnd The field index ending position
    * @param int|string $start The index starting position
    * @param int $length The length count
    */
    public function setFieldIndexStartLength($start,$length)
    {
        $_startEnd = $this->validateStartLength($start,$length);
        $this->fieldIndexStart = $_startEnd[0];
        $this->fieldIndexEnd = $_startEnd[1];
    }
    
    /**
    *
    * Get the character starting position
    *
    * @api
    * @access public
    * @return null|int|string MARCspec::$fieldIndexStart The field index starting position
    */
    public function getFieldIndexStart()
    {
        return (isset($this->fieldIndexStart)) ? $this->fieldIndexStart : null;
    }
    
    /**
    *
    * Get the field index ending position
    *
    * @api
    * @access public
    * @return null|int MARCspec::$fieldIndexEnd The field index ending position
    */
    public function getFieldIndexEnd()
    {
        return (isset($this->fieldIndexEnd)) ? $this->fieldIndexEnd : null;
    }
    
    /**
    * get SubSpecs
    *
    * @api
    * @return null|array Array of subspecs
    */
    public function getSubSpecs()
    {
        return (0 < count($this->subSpecs)) ? $this->subSpecs : null;
    }
    
    /**
    * Serialize MARCspec as JSON
    */
    public function jsonSerialize() 
    {
        if(($fieldTag = $this->getFieldTag()) !== null) $_marcSpec['fieldTag'] = $fieldTag;
        if(($charStart = $this->getCharStart()) !== null) $_marcSpec['charStart'] = $charStart;
        if(($charEnd = $this->getCharEnd()) !== null) $_marcSpec['charEnd'] = $charEnd;
        if(($charLength = $this->getCharLength()) !== null) $_marcSpec['charLength'] = $charLength;
        if(($fieldIndexStart = $this->getFieldIndexStart()) !== null) $_marcSpec['fieldIndexStart'] = $fieldIndexStart;
        if(($fieldIndexEnd = $this->getFieldIndexEnd()) !== null) $_marcSpec['fieldIndexEnd'] = $fieldIndexEnd;
        if(($indicator1 = $this->getIndicator1()) !== null) $_marcSpec['indicator1'] = $indicator1;
        if(($indicator2 = $this->getIndicator2()) !== null) $_marcSpec['indicator2'] = $indicator2;
        if(($subfields = $this->getSubfields()) !== null) $_marcSpec['subfields'] = $subfields;
        return ['marcspec'=> $_marcSpec];
    }
    
    /**
    * Encode the MARCspec object as string
    *
    * @api
    * @access public
    * @uses MARCspec::checkFieldTag()   to check if field tag is set
    * @uses MARCspec::getFieldIndexStart()   to retrieve the field index starting position
    * @uses MARCspec::getFieldIndexEnd()   to retrieve the field index ending position
    * @uses MARCspec::getCharStart()   to retrieve the field character starting position
    * @uses MARCspec::getCharEnd()   to retrieve the field character ending position
    * @uses MARCspec::getSubspecs()   to retrieve the subspecs
    * @uses MARCspec::getSubfields()   to retrieve the subfield specs
    * @uses MARCspec::getIndicator1()   to retrieve the indicator 1
    * @uses MARCspec::getIndicator2()   to retrieve the indicator 2
    * @param string $encoding The encoding "string", "json", "jsonpretty"
    */
    public function encode($encoding = 'string')
    {
        $this->checkFieldTag();
        if('string' == $encoding)
        {
            $marcspec = $this->fieldTag;
            if(!is_null($fieldIndexStart = $this->getFieldIndexStart()))
            {
                $marcspec .= '['.$fieldIndexStart;
                if(!is_null($fieldIndexEnd = $this->getFieldIndexEnd()))
                {
                    $marcspec .= '-'.$fieldIndexEnd;
                }
                $marcspec .= ']';
            }
            if(!is_null($charStart = $this->getCharStart()))
            {
                $marcspec .= '/'.$charStart;
                
                if(!is_null($charEnd = $this->getCharEnd()))
                {
                    $marcspec .= '-'.$charEnd;
                }
                
                return $marcspec;
            }
            if(!is_null($_subfields = $this->getSubfields()))
            {
                foreach($_subfields as $subfieldTag => $_sfSpec)
                {
                    if($_sfSpec['start'] === 0 && !array_key_exists('end',$_sfSpec))
                    {
                        $marcspec .= '$'.$subfieldTag;
                    }
                    else
                    {
                        $marcspec .= '$'.$subfieldTag.'['.$_sfSpec['start'].'-';
                        if(array_key_exists('end',$_sfSpec))
                        {
                            $marcspec .= $_sfSpec['end'];
                        }
                        $marcspec .= ']';
                    }
                }
            }
            
            if(is_null($indicator1 = $this->getIndicator1()) & is_null($indicator2 = $this->getIndicator2()))
            {
                return $marcspec;
            }
            else
            {
                if(is_null($indicator2))
                {
                    return $marcspec .= '_'.$indicator1;
                }
                elseif(is_null($indicator1))
                {
                    return $marcspec .= '__'.$indicator2;
                }
                else
                {
                    return $marcspec .= '_'.$indicator1.$indicator2;
                }
            }
        } // end string encoding
        elseif('json' == $encoding)
        {
            return json_encode($this);
        }
        elseif('jsonpretty' == $encoding)
        {
            return json_encode($this,JSON_PRETTY_PRINT);
        }
    }


    /**
    *
    * Validate starting and ending position
    * 
    * @internal
    * @access private
    * @uses MARCspec::checkFieldTag()   to check if field tag is set
    * @param int|string $start The starting position
    * @param int|string $end The ending position
    * @return array $_startEnd index 0 => start, index 1 => end
    * @throws \InvalidArgumentException If the arguments are not positive integer, 0 or #
    * @throws \InvalidArgumentException If the second argument is a lower number than first argument
    */
    private function validateStartEnd($start,$end)
    {
        $this->checkFieldTag();
        $_startEnd = array();
        if(preg_match('/[0-9]/', $start))
        {
            $_startEnd[0] = (int)$start;
        }
        elseif('#' == $start)
        {
            $_startEnd[0] = '#';
        }
        else
        {
            throw new \InvalidArgumentException('First argument must be positive int, 0 or character #.');
        }
        
        if($start == $end || null === $end)
        {
            $_startEnd[1] = $_startEnd[0];
        }
        else
        {
            if(preg_match('/[0-9]/', $end))
            {
                if('#' != $_startEnd[0])
                {
                    if($end < $_startEnd[0])
                    {
                        throw new \InvalidArgumentException('Second argument must be higher number (or equal) than first argument.');
                    }
                    else
                    {
                        $_startEnd[1] = (int)$end;
                    }
                }
                else
                {
                    $_startEnd[1] = (int)$end;
                }
            }
            elseif('#' == $end)
            {
                $_startEnd[1] = '#';
            }
            else
            {
                throw new \InvalidArgumentException('Second argument must be positive int, 0 or character #.');
            }
        }
        return $_startEnd;
    }
    
    
    /**
    *
    * Validate starting position and length
    * 
    * @internal
    * @access private
    * @uses MARCspec::checkFieldTag()   to check if field tag is set
    * @param int|string $start The starting position
    * @param int$length $length The length count
    * @return array $_startEnd index 0 => start, index 1 => end
    * @throws \InvalidArgumentException If the first argument is not positive integer, 0 or #
    * @throws \InvalidArgumentException If the second argument is not positive integer without 0
    */
    private function validateStartLength($start,$length)
    {
        $this->checkFieldTag();
        $_startEnd = array();
        if(preg_match('/[0-9]/', $start))
        {
            $_startEnd[0] = (int)$start;
        }
        elseif('#' == $start)
        {
            $_startEnd[0] = '#';
        }
        else
        {
            throw new \InvalidArgumentException('First argument must be positive int, 0 or character #.');
        }
        
        
        if(preg_match('/^[1-9]\d*/', $length)) // only positive int without 0
        {
            if('#' != $_startEnd[0])
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
            throw new \InvalidArgumentException('Second argument must be positive int without 0.');
        }
        return $_startEnd;
    }
    
    
    
    /**
    * set subfield character position or range
    * 
    * @internal
    * @access private
    * @param string $subfield A subfield tag
    * @param string $charposSpec A character position or range spec
    */
    private function setSubfieldCharPos($subfield,$charposSpec)
    {
        if('/' != $charposSpec[0]) throw new \InvalidArgumentException('Assuming subfield character position or range. Missing "/" delimiter in. "'.$charposSpec[2].'".');
        if(strlen($charposSpec[2]) < 2) throw new \InvalidArgumentException('Assuming subfield character position or range. String length must be 2 at minimum. "'.$charposSpec[2].'" is only '.strlen($charposSpec[2]).' characters long.');
        
        $charPos = substr($charposSpec[2],1);
        $_charPos = $this->validateCharPos($charPos);
        
        $this->subfields[$subfield]['charpos']['start'] = ('#' == $_charPos[0]) ? '#' : (int)$_charPos[0];
        
        if(array_key_exists('1',$_charPos))
        {
            if(!empty($_charPos[1])) 
            {
                $this->subfields[$subfield]['charpos']['end'] = ('#' == $_charPos[1]) ? '#' : (int)$_charPos[1];
            }
        }
        else
        {
            $this->subfields[$subfield]['charpos']['end'] = $this->subfields[$subfield[0]]['charpos']['start'];
        }
    }
    
    /**
    * validates and sets subfields
    *
    * @internal
    * @access private
    * @param string $arg The subfield tag
    * @uses MARCspec::validateCharPos()         To validate the character position or range
    * @property-write array MARCspec::$subfields The array of subfields
    * @property-write string MARCspec::$currentSubfield The currently added subfield
    * @property-write int|string $charEnd The character ending position
    * @throws \InvalidArgumentException if arg is empty
    * @throws \InvalidArgumentException for assumed subfield index if arg length is less than 4
    * @throws \InvalidArgumentException for assumed subfield index if closing bracket is missing
    * @throws \InvalidArgumentException for assumed subfield index if syntax is bad
    * @throws \InvalidArgumentException for assumed subfield range if hyphen is missing
    * @throws \InvalidArgumentException for assumed subfield range if arg length is greater than 3
    * @throws \InvalidArgumentException for assumed subfield range if syntax is bad
    */
    private function validateSubfield($arg)
    {
        if(empty($arg))
        {
            throw new \InvalidArgumentException('Unexpected empty subfield tag');
        }
        $argLength = strlen($arg);

        if($argLength > 1) // assuming index, subfield range or character position or range
        {
            $_split = preg_split('/\[(.*)\]/',$arg,-1,PREG_SPLIT_DELIM_CAPTURE);
            if(3 == count($_split)) // assuming index and character position or range
            {
                if($argLength < 4) throw new \InvalidArgumentException('Assuming subfield index. String length must be 4 at minimum. "'.$arg.'" is only '.$argLength.' characters long.');

                $_index = $this->validateCharPos($_split[1]);
                
                $this->subfields[$arg[0]]['tag']   = $arg[0];
                $this->subfields[$arg[0]]['index']['start'] = ('#' == $_index[0]) ? '#' : (int)$_index[0];
                $this->currentSubfield = $arg[0];
                
                if(array_key_exists('1',$_index))
                {
                    if(!empty($_index[1])) 
                    {
                        $this->subfields[$arg[0]]['index']['end'] = ('#' == $_index[1]) ? '#' : (int)$_index[1];
                    }
                }
                else
                {
                    $this->subfields[$arg[0]]['index']['end'] = $this->subfields[$arg[0]]['index']['start'];
                }

                if(!empty($_split[2])) // assuming character position or range
                {
                    $this->setSubfieldCharPos($arg[0],$_split[2]);
                }
                // set tag per default
                $this->subfields[$arg[0]]['tag']   = $arg[0];
                $this->currentSubfield = $arg[0];
            }
            elseif(1 == count($_split) && '/' == $arg[1]) // assuming character position or range
            {
                $this->setSubfieldCharPos($arg[0],substr($arg,1));
            }
            elseif(1 == count($_split) && '-' == $arg[1]) // assuming subfield range
            {
                if(!substr($arg,1,1) == '-')
                {
                    throw new \InvalidArgumentException('Assuming subfield range. But missing "-" in "'.$arg.'".');
                }
                elseif($argLength !== 3) 
                {
                    throw new \InvalidArgumentException('Assuming subfield range. String length must be 3. '.$argLength.' given.');
                }
                elseif(preg_match('/[a-z]/', $arg[0]) && !preg_match('/[a-z]/', $arg[2]))
                {
                    throw new \InvalidArgumentException('Assuming subfield range. Only ranges between "a-z", "A-Z" or "0-9" allowed. "'.$arg.'" given.');
                }
                elseif(preg_match('/[A-Z]/', $arg[0]) && !preg_match('/[A-Z]/', $arg[2]))
                {
                    throw new \InvalidArgumentException('Assuming subfield range. Only ranges between "a-z", "A-Z" or "0-9" allowed. "'.$arg.'" given.');
                }
                elseif(preg_match('/[0-9]/', $arg[0]) && !preg_match('/[0-9]/', $arg[2]))
                {
                    throw new \InvalidArgumentException('Assuming subfield range. Only ranges between "a-z", "A-Z" or "0-9" allowed. "'.$arg.'" given.');
                }
                else
                {
                    foreach(range($arg[0],$arg[2]) as $sfStep)
                    {
                        $this->subfields[$sfStep]['tag'] = $sfStep;
                        $this->subfields[$sfStep]['start'] = 0;
                        $this->currentSubfield = $sfStep;
                    }
                }
            }
            else
            {
                throw new \InvalidArgumentException('Subfield spec length is greater than 1. But no subfield range, index or character position or range could be detected in "'.$arg.'".');
            }
        }
        else // simple subfield
        {
            $this->subfields[$arg]['tag'] = $arg;
            $this->subfields[$arg]['start'] = 0;
            $this->currentSubfield = $arg;
        }
    }
    
    /**
    * validates a spec
    *
    * @internal
    * @access private
    * @uses MARCspec::checkIfString()           to check if spec is a string
    * @uses MARCspec::clear()                   to reset all properties
    * @uses MARCspec::setFieldTag()             to set the field tag
    * @uses MARCspec::validateCharPos()         to validate the character position or range
    * @uses MARCspec::setFieldIndexStartEnd()   to set the field index starting and ending position
    * @uses MARCspec::setCharStartEnd()         to set character starting and ending position
    * @uses MARCspec::detectSubSpecs()          to detect subspecs
    * @uses MARCspec::analyzeSubSpec()          to analyze subspecs
    * @uses MARCspec::splitDataRef()            to parse dataRef
    * @param string $spec The Marc spec as string
    * @throws \InvalidArgumentException if the argument is less than 3 chars long
    * @throws \InvalidArgumentException if a whitespace is used within the spec
    * @throws \InvalidArgumentException if character position is of length 0
    * @throws \InvalidArgumentException if spec is invalid
    */
    private function validate($spec)
    {
        $this->checkIfString($spec);
        $this->clear();
        $spec = trim($spec);
        $specLength = strlen($spec);
        // check string length
        if(3 > $specLength)
        {
            throw new \InvalidArgumentException("MARCspec must be 3 characters at minimum.");
        }
        if(preg_match('/\s/', $spec))
        {
            throw new \InvalidArgumentException('For Field Tag of MARCspec no whitespaces are allowed. But "'.$spec.'" given.');
        }
        
        /**
         * $specMatches[0] => whole spec
         * $specMatches[1] => fieldTag
         * $specMatches[2] => fieldIndexSpec
         * $specMatches[3] => fieldCharSpec
         * $specMatches[4] => rest
         */
        if(0 === preg_match('/^(.{3,3})(\[[0-9-#]*\]){0,1}(\/[0-9-#]*){0,1}(([{$_].*)|$)/',$spec,$specMatches))
        {
            throw new \InvalidArgumentException('Invalid MARCspec "'.$spec.'" given.');
        }
        
        // check and set field tag
        $this->setFieldTag($specMatches[1]);
        
        if($specLength > 3)
        {
            // check an set fieldIndexSpec
            if(!array_key_exists(2,$specMatches))
            {
                throw new \InvalidArgumentException('Assuming invalid field index. "'.$spec.'" given.');
            }
            
            if(!empty($specMatches[2]))
            {
                if( preg_match('/^\[(.*)\]/', $specMatches[2], $_assumedIndex) )
                {
                    if( $_index = $this->validateCharPos($_assumedIndex[1]) )
                    {
                        $fieldIndexEnd = null;
                        if(array_key_exists('1',$_index))
                        {
                            if(!empty($_index[1]))
                            {
                                $fieldIndexEnd = $_index[1];
                            }
                        }
                        $this->setFieldIndexStartEnd($_index[0],$fieldIndexEnd);
                    }
                }
                else
                {
                    throw new \InvalidArgumentException('Assuming invalid field index. ""'.$spec.'" given.');
                }
            }
            
             // check an set fieldCharSpec
            if(!array_key_exists(3,$specMatches))
            {
                throw new \InvalidArgumentException('Assuming invalid character position or range. "'.$spec.'" given.');
            }
   
            if(!empty($specMatches[3]))
            {
                // check character postion or range
                $charPosOrRange = substr($specMatches[3], 1);
                if('' != $charPosOrRange)
                {
                    if($_charPosOrRange = $this->validateCharPos($charPosOrRange))
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
                    throw new \InvalidArgumentException('For character position or range minimum one digit or character # is required. None given.');
                }
            }
            
            // process rest
            if(!empty($specMatches[4]))
            {
                $dataRef = $specMatches[4];
                if('{' == $specMatches[4][0]) // assuming subSpec
                {
                    do
                    {
                        $rest = $this->detectSubSpecs($specMatches[4]);
                    }
                    while(!empty($rest) && '{' == $rest[0]);
                    
                    if(!empty($rest))
                    {
                        $this->splitDataRef($rest);
                    }
                }
                else
                {
                    $this->splitDataRef($dataRef);
                }
            }
        }
        return true;
    }
    
    /**
    * detect subSpecs
    * 
    * Counts opening and closing brackets
    * detects operator and right and left subTerms
    * 
    * @internal
    * @access private
    * @uses MARCspec::createSubSpec()   to create a subspec
    * @param string $assumedSubspecs A string with assumed subSpecs
    * @return string $rest The rest after first subSpec
    * @throws \InvalidArgumentException If at the end the count of closing brackets not equals the count of opening brackets
    * @throws \InvalidArgumentException If right subTerm is empty
    */
    private function detectSubSpecs($assumedSubspecs)
    {
        $open = 1;
        $close = 0;
        $_nocount = ['$','\\'];
        $_operators = ['?','!','~','='];
        $operator = null;
        $leftSubTerm = '';
        for($i = 1;$i < strlen($assumedSubspecs);$i++)
        {
            
            if('{' == $assumedSubspecs[$i])
            {
                if(!in_array($assumedSubspecs[$i-1],$_nocount))
                {
                    $open = $open +1;
                }
                elseif( $i > 1 && '\\' == $assumedSubspecs[$i-2] ) // if something like \$} or \\} occurs
                {
                    $open = $open +1;
                }
            }
            elseif('}' == $assumedSubspecs[$i])
            {
                if(!in_array($assumedSubspecs[$i-1],$_nocount))
                {
                    $close = $close +1;
                }
                elseif( $i > 1 && '\\' == $assumedSubspecs[$i-2] )
                {
                    $close = $close +1;
                }
            }
            
            if(in_array($assumedSubspecs[$i],$_operators) && !in_array($assumedSubspecs[$i-1],$_nocount))
            {
                if($open -1 === $close)
                {
                    $operator .= $assumedSubspecs[$i];
                    $pos = $i;
                    $len = strlen($operator);
                }
            }
            
            if($close === $open)
            {
                if(!is_null($operator))
                {
                    $operatorStartPos = $pos + 1 - $len;
                    $leftSubTerm = substr($assumedSubspecs,1,$operatorStartPos-1); // might be empty
                    $rightSubTerm = substr($assumedSubspecs,$pos + 1,$i-$pos-1);
                    if(empty($rightSubTerm))
                    {
                        throw new \InvalidArgumentException('Assuming invalid subSpec. '.$assumedSubspecs.' given. Right hand subSpec is missing.');
                    }
                }
                else
                {
                    $operator = '?';
                    $rightSubTerm = substr($assumedSubspecs,1,$i-1);
                }
                $this->createSubSpec($leftSubTerm,$rightSubTerm,$operator);
                return substr($assumedSubspecs,$i+1);
            }
        }
        throw new \InvalidArgumentException('Assuming invalid subSpec. '.$assumedSubspecs.' given.');
    }
    
    /*
    * creates a subSpec
    * 
    * @internal
    * @access private
    * @property-write array The array of subspecs
    * @uses MARCspec:normalizeComparisonString()    to normalize a comparison string
    * @uses MARCspec:getFieldTag()                  to retrieve the field tag
    * @param string $leftSubTerm the left hand subterm
    * @param string $rightSubTerm the right hand subterm
    * @param string $operator the subspecs operator
    */
    private function createSubSpec($leftSubTerm,$rightSubTerm,$operator)
    {
        $_subSpec['operator'] = $operator;
        foreach(['leftSubTerm'=>$leftSubTerm,'rightSubTerm'=>$rightSubTerm] as $subTermKey => $subTerm)
        {
            if(!empty($subTerm))
            {
                if('\\' == $subTerm[0]) // is a comparisonString
                {
                    $_subSpec[$subTermKey] = $this->normalizeComparisonString($subTerm);
                }
                else
                {
                    switch($subTerm[0]) 
                    {
                        case '[': $currentSpec = $this->getFieldTag().$subTerm;
                        break;
                        case '/': $currentSpec = $this->getFieldTag();
                        break;
                       # case '$': 
                       # break;
                       # case: '_':
                       # break;
                       # default:
                    }
                }
            }
            else
            {
                
            }
        $this->subSpecs[] = '';
        }
        
    }
    /**
    * validate a field tag
    * 
    * @internal
    * @access private
    * @param string $fieldTag The MARC spec as string field tag
    * @throws \InvalidArgumentException If the argument consits of characters not allowed
    * @return true if string is a valid field tag
    */
    private function validateFieldTag($fieldTag)
    {
        if(!preg_match('/[.0-9a-z]{3,3}|[.0-9A-Z]{3,3}/', $fieldTag))
        {
            throw new \InvalidArgumentException('For Field Tag of MARCspec only "." and digits and lowercase alphabetic or digits and upper case alphabetics characters are allowed. But "'.$fieldTag.'" given.');
        }
        return true;
    }
    
    /**
    * validate a character position or range
    * 
    * @internal
    * @access private
    * @param string $charPos The character position or range
    * @throws \InvalidArgumentException if the argument is less than 1 char long
    * @throws \InvalidArgumentException if it consits of characters not allowed
    * @throws \InvalidArgumentException for index or character range if argument is less than 3 characters long
    * @throws \InvalidArgumentException for index or character range if first character is "-"
    * @throws \InvalidArgumentException for index or character range if more than one "-" is present
    * @return array $charPos An array of character positions
    */
    private function validateCharPos($charPos)
    {
        $charPosLength = strlen($charPos);
        if(1 > $charPosLength)
        {
            throw new \InvalidArgumentException('Assuming index or character position or range. Minimum one character is required. None given.');
        }
        for($x = 0; $x < $charPosLength; $x++)
        {
            if(!preg_match('/[0-9-#]/', $charPos[$x]))
            {
                throw new \InvalidArgumentException('Assuming index or character position or range. Only digits, the character # and one "-" is allowed. But "'.$charPos.'" given.');
            }
        }
        if(strstr($charPos,'-') && 3 < $charPosLength) // something like 1- is not valid
        {
            throw new \InvalidArgumentException('Assuming index or character range. At least two digits or the character # must be present. "'.$charPos.'" given.');
        }
        if(0 === strpos($charPos,'-'))
        {
            throw new \InvalidArgumentException('Assuming index or character position or range. First character must not be "-". "'.$charPos.'" given.');
        }
        if(strpos($charPos,'-') !== strrpos($charPos,'-'))
        {
            throw new \InvalidArgumentException('Assuming index or character position or range. Only one "-" character allowed. But "'.$charPos.'" given.');
        }
        $_charPos = explode('-',$charPos);
        if(2 < count($_charPos))
        {
            throw new \InvalidArgumentException('Assuming index or character position or range. Only digits and one "-" is allowed. But "'.$charPos.'" given.');
        }
        return $_charPos;
    }
    
    
    /**
    * split subfield tags and indicators
    * 
    * @internal
    * @access private
    * @uses MARCspec::setIndicators()   to set indicators
    * @uses MARCspec::addSubfields()    to add subfields
    * @param string $dataFieldRef The specification for subfields and indicators
    *
    */
    private function splitDataRef($dataFieldRef)
    {
        if('_' == $dataFieldRef[0])
        {
            $_ref = explode('$',$dataFieldRef,2);
            
            $this->setIndicators(substr($_ref[0],1));
            if(array_key_exists(1,$_ref))
            {
                $this->addSubfields($_ref[1]);
            }
        }
        elseif('$' == $dataFieldRef[0])
        {
            if($lastIndexOf_ = strrpos($dataFieldRef,'_'))
            {
                if(substr($dataFieldRef,$lastIndexOf_-1,1) != '$') // assuming indicators given
                {
                    $indicators = substr($dataFieldRef,$lastIndexOf_+1);
                    $this->setIndicators($indicators);
                    $this->addSubfields( substr($dataFieldRef,0,(strlen($indicators)+1)*-1) );
                }
            }
            else // assuming no indicators given
            {
                $this->addSubfields($dataFieldRef);
            }
        }
        else
        {
            throw new \InvalidArgumentException('Either subfield specs are not valid or indicators are not prefixed correctly. "'.$dataFieldRef.'" given.');
        }
    }
    
    /**
    * validate indicators
    * 
    * @internal
    * @access private
    * @param string $indicators The MARC spec as string indicators
    * @throws \InvalidArgumentException if the argument is more than 2 chars long,
    * @throws \InvalidArgumentException if it consits of characters not allowed
    * @return true if $indicators is a valid indicators spec
    */
    private function validateIndicators($indicators)
    {
            $indLength = strlen($indicators);
            if(2 < $indLength)
            {
                throw new \InvalidArgumentException('For indicators only two characters at are allowed. "'.$indLength.'" characters given.');
            }
            elseif(preg_match('/_{2,2}/', $indicators))
            {
                throw new \InvalidArgumentException('At minimum one indicator must be a digit or a lowercase alphabetic character. But "'.$indicators.'" given.');
            }
            for($x = 0; $x < strlen($indicators); $x++)
            {
                if(!preg_match('/[a-z0-9_]/', $indicators[$x]))
                {
                    throw new \InvalidArgumentException('For indicators only digits, lowercase alphabetic characters and "_" are allowed. But "'.$indicators.'" given.');
                }
            }
            return true;
    }

    /**
    * checks if argument is a string
    * 
    * @internal
    * @access private
    * @param string $arg The argument to check
    * @throws \InvalidArgumentException if the argument is not a string
    */
    private function checkIfString($arg)
    {
        if(!is_string($arg)) throw new \InvalidArgumentException("Method decode only accepts string as argument. Given " .gettype($arg).".");
    }
    
    /**
    * clear all properties
    * 
    * @internal
    * @access private
    */
    private function clear()
    {
        unset(
            $this->fieldTag,
            $this->charStart,
            $this->charEnd,
            $this->indicator1,
            $this->indicator2,
            $this->fieldIndexStart,
            $this->fieldIndexEnd,
            $this->currentSpec,
            $this->currentSubfield
        );
        $this->subfields = array();
        $this->subSpecs = array();
    }

    /**
    * check if field tag is set
    * 
    * @internal
    * @access private
    * @throws \Exception If field tag is not set
    */
    private function checkFieldTag()
    {
        if($this->getFieldTag() == null)
        {
            throw new \Exception("Field tag must be set first. Use CK\MARCspec::setFieldTag() first.");
        }
        else
        {
            return true;
        }
    }
} // EOC