<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MarcSpec;

/**
* Class to decode, validate and encode MARC spec as string.
* For Specification of MARC spec as string see
* <http://cklee.github.io/marc-spec/marc-spec.html>
*/
class MarcSpec implements \JsonSerializable{

    /**
    * @var string field tag
    */
    private $fieldTag;
    
    /**
    * @var int starting character position
    */
    private $charStart;
    
    /**
    * @var int ending character position
    */
    private $charEnd;
    
    /**
    * @var int length of character position range
    */
    private $charLength;
    
    /**
    * @var array Associative array of subfield tags as key and value
    */
    private $subfields = array();
    
    /**
    * @var array Associative array of subfield indizes
    */
    private $subfieldIndex = array();
    
    /**
    * @var string indicator 1
    */
    private $indicator1;
    
    /**
    * @var string indicator 2
    */
    private $indicator2;
    
    /**
    * @var int fieldIndexStart
    */
    private $fieldIndexStart;
    
    /**
    * @var int fieldIndexEnd
    */
    private $fieldIndexEnd;
    
    
    /**
    * Constructor
    *
    * @param string
    */
    public function __construct($spec = false)
    {
        if($spec) $this->decode($spec);
    }
    
   /**
   * parse a MARC spec as string into an object
   *
   * @access public
   *
   * @param string $spec   The Marc spec as string
   */
    public function decode($spec)
    {
        $this->validate($spec);
    }
    
    /**
    * Encode the MarcSpec object as string
    *
    * @access public
    *
    * 
    */
    public function encode($encoding = "string")
    {
        if(!isset($this->fieldTag)) throw new \Exception("No field tag available. Assuming MarcSpec is not initialized.");
        if("string" == $encoding)
        {
            $marcspec = $this->fieldTag;
            if(!is_null($fieldIndexStart = $this->getFieldIndexStart()))
            {
                $marcspec .= "[".$fieldIndexStart;
                if(!is_null($fieldIndexEnd = $this->getFieldIndexEnd()))
                {
                    $marcspec .= "-".$fieldIndexEnd;
                }
                $marcspec .= "]";
            }
            if(!is_null($charStart = $this->getCharStart()))
            {
                $marcspec .= "/".$charStart;
                
                if(!is_null($charEnd = $this->getCharEnd()))
                {
                    $marcspec .= "-".$charEnd;
                }
                
                return $marcspec;
            }
            
            if(!is_null($_subfields = $this->getSubfields()))
            {
                foreach($_subfields as $subfieldTag => $_sfSpec)
                {
                    if($_sfSpec['start'] === 0 && !array_key_exists('end',$_sfSpec))
                    {
                        $marcspec .= "$".$subfieldTag;
                    }
                    else
                    {
                        $marcspec .= "$".$subfieldTag."[".$_sfSpec['start']."-";
                        if(array_key_exists('end',$_sfSpec))
                        {
                            $marcspec .= $_sfSpec['end'];
                        }
                        $marcspec .= "]";
                    }
                }
                #var_dump($_subfields);
                #$marcspec .= implode("",$_subfields);
            }
            
            if(is_null($indicator1 = $this->getIndicator1()) & is_null($indicator2 = $this->getIndicator2()))
            {
                return $marcspec;
            }
            else
            {
                if(is_null($indicator2))
                {
                    return $marcspec .= "_".$indicator1;
                }
                elseif(is_null($indicator1))
                {
                    return $marcspec .= "__".$indicator2;
                }
                else
                {
                    return $marcspec .= "_".$indicator1.$indicator2;
                }
            }
        } // end string encoding
        elseif("json" == $encoding)
        {
            return json_encode($this,JSON_PRETTY_PRINT);
        }
    }

    /**
    *
    * Set the field tag
    *
    * Provided pram gets validated
    *
    * @access public
    *
    * @param string $arg    The field tag
    */
    public function setFieldTag($arg)
    {
        if($this->validateFieldTag($arg)) $this->fieldTag = $arg;
    }
    
    /**
    *
    * Get the field tag
    *
    * @access public
    *
    * @return null | string
    */
    public function getFieldTag()
    {
        return (isset($this->fieldTag)) ? $this->fieldTag : null;
    }
    
    /**
    *
    * Set the character starting position
    *
    * Length of character range automatically is set 
    * if character ending position is set.
    *
    * @access public
    *
    * @param int $arg
    *
    * @throws \InvalidArgumentException If the argument is not positive integer or 0
    */
    public function setCharStart($arg)
    {
        $this->checkFieldTag();
        if(is_int($arg) && 0 <= $arg)
        {
            $this->charStart = $arg;
            if(isset($this->charEnd))
            {
                $this->charLength = $this->charEnd - $this->charStart + 1;
            }
        }
        else
        {
            throw new \InvalidArgumentException('Argument must be of type int.');
        }
    }
    
    /**
    *
    * Get the character starting position
    *
    * @access public
    *
    * @return null | int
    */
    public function getCharStart()
    {
        return (isset($this->charStart)) ? $this->charStart : null;
    }
    
    /**
    *
    * Set the character ending position
    *
    * Length of character range automatically is set 
    * if character starting position is set.
    *
    * @access public
    *
    * @param int $arg
    *
    * @throws \InvalidArgumentException If the argument is not positive integer or 0
    * @throws \Exception                If character starting position is not set
    */
    public function setCharEnd($arg)
    {
        $this->checkFieldTag();
        if(is_int($arg) && 0 <= $arg)
        {
            if(!isset($this->charStart))
            {
                throw new \Exception("Character start position must be defined first. Use MarcSpec::setCharStart() first to set the character start position.");
            }
            else
            {
                $this->charEnd = $arg;
                $this->charLength = $this->charEnd - $this->charStart + 1;
            }
        }
        else
        {
            throw new \InvalidArgumentException('Argument must be of type positive int or 0.');
        }
    }
    
    /**
    *
    * Get the character ending position
    *
    * @access public
    *
    * @return null | int
    */
    public function getCharEnd()
    {
        if(isset($this->charEnd))
        {
            return $this->charEnd;
        }
        elseif(isset($this->charStart) && isset($this->charLength))
        {
            return $this->charStart + $this->charLength - 1;
        }
        else
        {
            return null;
        }
    }
    
    /**
    *
    * Set the length of character range
    *
    * Character starting position must be set first.
    *
    * Ending character position automatically is set
    *
    * @access public
    *
    * @param int $arg
    *
    * @throws \InvalidArgumentException If the argument is not positive integer without 0.
    * @throws \Exception                If character starting position is not set
    */
    public function setCharLength($arg)
    {
        $this->checkFieldTag();
        if(is_int($arg) && 0 < $arg)
        {
            if(!isset($this->charStart))
            {
                throw new \Exception("Character start position must be defined first. Use MarcSpec::setCharStart() first to set the character start position.");
            }
            $this->charLength = $arg;
            $charEnd = $this->charStart + $this->charLength - 1;
            $this->setCharEnd($charEnd);
        }
        else
        {
            throw new \InvalidArgumentException('Argument must be of type positive int without 0.');
        }
    }
    
    /**
    *
    * Get length of character range
    *
    * @access public
    *
    * @return null | int
    */
    public function getCharLength()
    {
        if(isset($this->charLength))
        {
            return $this->charLength;
        }
        elseif(isset($this->charStart) && isset($this->charEnd))
        {
            return $this->charEnd - $this->charStart + 1;
        }
        else
        {
            return null;
        }
    }
    
    /**
    * validates and sets subfields
    *
    * @access private
    *
    * @param string $arg    The subfield tag
    *
    * @return bool  True if subfield is valid
    */
    private function validateSubfield($arg)
    {
        $this->checkIfString($arg);
        if("" == $arg)
        {
            throw new \InvalidArgumentException('Unexpected empty subfield tag');
        }
        $argLength = strlen($arg);

        if($argLength > 1) // assuming index or subfield range
        {
            if($arg[1] == "[") // assuming index
            {
                if($argLength < 4)
                {
                    throw new \InvalidArgumentException('Assuming subfield index. String length must be 4 at minimum. "'.$arg.'" has only '.$argLength.'.');
                }
                else
                {
                    if(!strstr($arg,"]"))
                    {
                        throw new \InvalidArgumentException('Assuming subfield index. Missing closing bracket in "'.$arg.'".');
                    }
                    elseif(strpos($arg,"[") > strpos($arg,"]"))
                    {
                        throw new \InvalidArgumentException('Assuming subfield index. Bad open and closing bracket order in "'.$arg.'".');
                    }
                    elseif(strpos($arg,"[") !== 1)
                    {
                        
                    }
                    else
                    {
                        if(preg_match('/\[(.*)]/', $arg, $_assumedIndex))
                        {
                            $_index = $this->validateCharPos($_assumedIndex[1]);
                            
                            $this->subfields[$arg[0]]['tag']   = $arg[0];
                            $this->subfields[$arg[0]]['start'] = (int)$_index[0];
                            
                            if(array_key_exists("1",$_index))
                            {
                                if(!empty($_index[1])) $this->subfields[$arg[0]]['end'] = (int)$_index[1];
                            }
                            else
                            {
                                $this->subfields[$arg[0]]['end'] = $this->subfields[$arg[0]]['start'];
                            }
                        }
                        else
                        {
                            throw new \InvalidArgumentException('Assuming subfield index. Unknown error when parsing "'.$arg.'".');
                        }
                    }
                }
            }
            else // assuming subfield range
            {
                if(!substr($arg,1,1) == "-")
                {
                    throw new \InvalidArgumentException('Assuming subfield range. But missing "-" in "'.$arg.'".');
                }
                elseif($argLength !== 3) 
                {
                    throw new \InvalidArgumentException('Assuming subfield range. String length is to high in "'.$arg.'".');
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
                    }
                    return true;
                }
            }
        }
        else // assuming no index or subfield range
        {
            $this->subfields[$arg]['tag'] = $arg;
            $this->subfields[$arg]['start'] = 0;
            return true;
        }
    
    }
    
    /**
    *
    * Add subfield tag
    *
    * Adds a subfield tag to the array of subfield tags
    *
    * @access public
    *
    * @param string $arg
    *
    * @return bool  True if subfields tags added to subfields
    *               False if no subfields are accepted because of
    *               Leader or control fields
    */
    public function addSubfields($arg)
    {
        $this->checkFieldTag();
        if(isset($this->charStart) or "LDR" === $this->fieldTag)
        {
            throw new \InvalidArgumentException('For leader or control fields subfield spec is not allowed.');
        }
        $this->checkIfString($arg);
        for($x = 0; $x < strlen($arg); $x++)
        {
            if(!preg_match("/[!\"#$%&'()*+,-.\/0-9:;<=>?[\]^_`a-z{}~]/", $arg[$x]))
            {
                throw new \InvalidArgumentException('For subfields only digits, lowercase alphabetic characters or one of "!"#$%&\'()*+,-./0-9:;<=>?[\]^_`a-z{}~" are allowed. But "'.$arg.'" given.');
            }
        }
        $sf_save = str_replace("$$","$|",$arg); // save subfield tag $
        $_subfields = explode("$",$sf_save);
        foreach(array_filter($_subfields) as $sf)
        {
            if("|" == $sf[0])
            {
                $sf[0] = "$"; // undo save subfield tag $
            }
            $this->validateSubfield($sf);
        }
    }
    
    /**
    *
    * Get subfield tags
    *
    * @access public
    *
    * @return null | array
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
    * @access public
    *
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
                    if("_" != $arg[$x]) $this->setIndicator1($arg[$x]);
                }
                if(1 === $x)
                {
                    if("_" != $arg[$x]) $this->setIndicator2($arg[$x]);
                }
            }
        }
    }
    
    /**
    *
    * Set indicator 1
    *
    * Argument gets validated
    *
    * @access public
    *
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
    * @access public
    *
    * @return null | string
    */
    public function getIndicator1()
    {
        return (isset($this->indicator1)) ? $this->indicator1 : null;
    }
    
    /**
    *
    * Set indicator 2
    *
    * Argument gets validated
    *
    * @access public
    *
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
    * @access public
    *
    * @return null | string
    */
    public function getIndicator2()
    {
        return (isset($this->indicator2)) ? $this->indicator2 : null;
    }
    
    /**
    *
    * Set the field index starting position
    *
    *
    * @access public
    *
    * @param int $arg
    *
    * @throws \InvalidArgumentException If the argument is not positive integer or 0
    */
    public function setFieldIndexStart($arg)
    {
        $this->checkFieldTag();
        if(is_int($arg) && 0 <= $arg)
        {
            $this->fieldIndexStart = $arg;
        }
        else
        {
            throw new \InvalidArgumentException('Argument must be of type int.');
        }
    }
    
    /**
    *
    * Get the character starting position
    *
    * @access public
    *
    * @return null | int
    */
    public function getFieldIndexStart()
    {
        return (isset($this->fieldIndexStart)) ? $this->fieldIndexStart : null;
    }
    
    /**
    *
    * Set the index ending position
    *
    *
    * @access public
    *
    * @param int $arg
    *
    * @throws \InvalidArgumentException If the argument is not positive integer or 0
    * @throws \Exception                If index starting position is not set
    */
    public function setFieldIndexEnd($arg)
    {
        $this->checkFieldTag();
        if(is_int($arg) && 0 <= $arg)
        {
            if(!isset($this->fieldIndexStart))
            {
                throw new \Exception("Field index start position must be defined first. Use MarcSpec::setFieldIndexStart() first to set the field index start position.");
            }
            else
            {
                $this->fieldIndexEnd = $arg;
            }
        }
        else
        {
            throw new \InvalidArgumentException('Argument must be of type positive int or 0.');
        }
    }
    
    /**
    *
    * Get the field index ending position
    *
    * @access public
    *
    * @return null | int
    */
    public function getFieldIndexEnd()
    {
        if(isset($this->fieldIndexEnd))
        {
            return $this->fieldIndexEnd;
        }
        else
        {
            return null;
        }
    }
    
    /**
    * validates a MARC spec as string
    *
    * @access public
    *
    * @param string $spec   The Marc spec as string
    *
    * @throws \InvalidArgumentException If the argument is less than 3 chars long,
    *                                   if a whitespace is used within the spec,
    *                                   if character position or range is less than 1
    *                                   character
    */
    public function validate($spec)
    {
        $this->checkIfString($spec);
        $this->clear();
        $spec = trim($spec);
        
        // check string length
        if(3 > strlen($spec))
        {
            throw new \InvalidArgumentException("MARCspec must be 3 characters at minimum.");
        }
        if(preg_match('/\s/', $spec))
        {
            throw new \InvalidArgumentException('For Field Tag of MARCspec no whitespaces are allowed. But "'.$spec.'" given.');
        }
        // check and set field tag
        $this->setFieldTag(substr($spec, 0, 3));
        
        if(3 < strlen($spec))
        {
            $indexEnd = 2; // default index to continue without field index
            if("[" == substr($spec, 3, 1))
            {
                if($indexEnd = strpos($spec,"]"))
                {
                    if( preg_match('/...\[(.*)].?/', $spec, $_assumedIndex) )
                    {
                        if( $_index = $this->validateCharPos($_assumedIndex[1]) )
                        {
                            $this->setFieldIndexStart((int)$_index[0]);
                            if(array_key_exists("1",$_index))
                            {
                                if(!empty($_index[1])) $this->setFieldIndexEnd((int)$_index[1]);
                            }
                            else
                            {
                                $this->setFieldIndexEnd($this->fieldIndexStart);
                            }
                        }
                    }
                    else
                    {
                        throw new \InvalidArgumentException('Assuming field index. But "'.$spec.'" given.');
                    }
                }
                else
                {
                    throw new \InvalidArgumentException('Assuming field index. No closing bracket "]" found. "'.$spec.'" given.');
                }
            }
            
            $dataRef = substr($spec, $indexEnd+1);
            if("" != $dataRef)
            {
                if("/" == substr($dataRef, 0, 1))
                {
                    // check character postion or range
                    $charPos = substr($dataRef, 1);
                    if("" != $charPos)
                    {
                        if($_charPos = $this->validateCharPos($charPos))
                        {
                            $this->setCharStart((int)$_charPos[0]);
                            if(array_key_exists(1,$_charPos))
                            {
                                if(!empty($_charPos[1])) $this->setCharEnd((int)$_charPos[1]);
                            }
                            else
                            {
                                $this->setCharLength(1);
                                $this->setCharEnd($this->charStart);
                            }
                        }
                    }
                    else
                    {
                        throw new \InvalidArgumentException('For character position or range minimum one digit is required. None given.');
                    }
                }
                else
                {
                    $this->parseDataRef($dataRef);
                }
            }
        }
        return true;
    }
    
    /**
    * validate a field tag
    *
    * @access private
    *
    * @param string $fieldTag   The MARC spec as string field tag
    *
    * @throws \InvalidArgumentException If the argument consits of characters not allowed
    *
    * @return true
    */
    private function validateFieldTag($fieldTag)
    {
        $this->checkIfString($fieldTag);
        
        if(!preg_match('/[.0-9a-z]{3,3}|[.0-9A-Z]{3,3}/', $fieldTag))
        {
            throw new \InvalidArgumentException('For Field Tag of MARCspec only "." and digits and lowercase alphabetic or digits and upper case alphabetics characters are allowed. But "'.$fieldTag.'" given.');
        }
        return true;
    }
    
    /**
    * validate a character position or range
    *
    * @access private
    *
    * @param string $charPos    The character position or range
    *
    * @throws \InvalidArgumentException If the argument is less than 1 char long,
    *                                   if it consits of characters not allowed
    *
    * @return array $charPos    An array of character positions
    */
    private function validateCharPos($charPos)
    {
        $this->checkIfString($charPos);
        $charPosLength = strlen($charPos);
        if(1 >$charPosLength)
        {
            throw new \InvalidArgumentException('Assuming index or charater position or range. Minimum one character is required. None given.');
        }
        for($x = 0; $x < $charPosLength; $x++)
        {
            if(!preg_match('/[0-9-]/', $charPos[$x]))
            {
                throw new \InvalidArgumentException('Assuming index or charater position or range. Only digits and one "-" is allowed. But "'.$charPos.'" given.');
            }
        }
        if(strstr($charPos,"-") && 2 > $charPosLength)
        {
            throw new \InvalidArgumentException('Assuming index or charater position or range. At least one digit must be present. "'.$charPos.'" given.');
        }
        if(0 === strpos($charPos,"-"))
        {
            throw new \InvalidArgumentException('Assuming index or charater position or range. First character must not be "-". "'.$charPos.'" given.');
        }
        if(strpos($charPos,"-") !== strrpos($charPos,"-"))
        {
            throw new \InvalidArgumentException('Assuming index or charater position or range. Only one "-" character allowed. But "'.$charPos.'" given.');
        }
        $_charPos = explode("-",$charPos);
        if(2 < count($_charPos))
        {
            throw new \InvalidArgumentException('Assuming index or charater position or range. Only digits and one "-" is allowed. But "'.$charPos.'" given.');
        }
        return $_charPos;
    }
    
    
    /**
    * validate subfield tags and indicators
    *
    * @access private
    *
    * @param string $dataFieldRef   The specification for subfields and indicators
    *
    */
    private function parseDataRef($dataFieldRef)
    {
        $this->checkIfString($dataFieldRef);
        
        if(substr($dataFieldRef,0,1) == "_")
        {
            $_ref = explode("$",$dataFieldRef,2);
            
            $this->setIndicators(substr($_ref[0],1));
            if(array_key_exists(1,$_ref))
            {
                $this->addSubfields($_ref[1]);
            }
        }
        elseif(substr($dataFieldRef,0,1) == "$")
        {
            if($lastIndexOf_ = strrpos($dataFieldRef,"_"))
            {
                if(substr($dataFieldRef,$lastIndexOf_-1,1) != "$") // assuming indicators given
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
    * @access private
    *
    * @param string $indicators The MARC spec as string indicators
    *
    * @throws \InvalidArgumentException If the argument is more than 2 chars long,
    *                                   if it consits of characters not allowed
    *
    * @return true
    */
    private function validateIndicators($indicators)
    {
            $this->checkIfString($indicators);
            $indLength = strlen($indicators);
            if(2 < $indLength)
            {
                throw new \InvalidArgumentException('For indicators only two characters at are allowed. "'.$indLength.'" characters given.');
            }
            elseif(preg_match("/_{2,2}/", $indicators))
            {
                throw new \InvalidArgumentException('At minimum one indicator must be a digit or a lowercase alphabetic character. But "'.$indicators.'" given.');
            }
            for($x = 0; $x < strlen($indicators); $x++)
            {
                if(!preg_match("/[a-z0-9_]/", $indicators[$x]))
                {
                    throw new \InvalidArgumentException('For indicators only digits, lowercase alphabetic characters and "_" are allowed. But "'.$indicators.'" given.');
                }
            }
            return true;
    }

    /**
    * checks if argument is a string
    *
    * @access private
    *
    * @param string $arg The argument to check
    *
    * @throws \InvalidArgumentException If the argument is not a string
    */
    private function checkIfString($arg)
    {
        if(!is_string($arg)) throw new \InvalidArgumentException("Method decode only accepts string as argument. Given " .gettype($arg).".");
    }
    
    /**
    * clear all properties
    * @access private
    */
    private function clear()
    {
        unset($this->fieldTag,$this->charStart,$this->charEnd,$this->charLength,$this->indicator1,$this->indicator2,$this->fieldIndexStart,$this->fieldIndexEnd);
        $this->subfields = array();
    }

    /**
    * check if field tag is set
    * @access private
    * @throws \Exception If field tag is not set
    */
    private function checkFieldTag()
    {
        if($this->getFieldTag() == null)
        {
            throw new \Exception("Field tag must be set first. Use CK\MarcSpec::setFieldTag() first.");
        }
        else
        {
            return true;
        }
    }
    
    public function jsonSerialize() {
        if(($fieldTag = $this->getFieldTag()) !== null) $_marcSpec["fieldTag"] = $fieldTag;
        if(($charStart = $this->getCharStart()) !== null) $_marcSpec["charStart"] = $charStart;
        if(($charEnd = $this->getCharEnd()) !== null) $_marcSpec["charEnd"] = $charEnd;
        if(($charLength = $this->getCharLength()) !== null) $_marcSpec["charLength"] = $charLength;
        if(($fieldIndexStart = $this->getFieldIndexStart()) !== null) $_marcSpec["fieldIndexStart"] = $fieldIndexStart;
        if(($fieldIndexEnd = $this->getFieldIndexEnd()) !== null) $_marcSpec["fieldIndexEnd"] = $fieldIndexEnd;
        if(($indicator1 = $this->getIndicator1()) !== null) $_marcSpec["indicator1"] = $indicator1;
        if(($indicator2 = $this->getIndicator2()) !== null) $_marcSpec["indicator2"] = $indicator2;
        if(($subfields = $this->getSubfields()) !== null) $_marcSpec["subfields"] = $subfields;
        return ["marcspec"=> $_marcSpec];
    }
}

$marcSpec = new MarcSpec('007/0-4');
#$marcSpec = new MarcSpec('300$a[1-]');
#print $marcSpec->getFieldTag();
print $marcSpec->encode('json');