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
class MarcSpec {

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
    * @var string indicator 1
    */
    private $indicator1;
    
    /**
    * @var string indicator 2
    */
    private $indicator2;

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
        $this->checkIfString($spec);
        $this->validate($spec);
    }
    
    /**
    * Encode the MarcSpec object as string
    *
    * @access public
    *
    * 
    */
    public function encode()
    {
        if(!isset($this->fieldTag)) throw new \Exception("No field tag available. Assuming MarcSpec is not initialized.");
        $marcspec = $this->fieldTag;
        if(!is_null($charStart = $this->getCharStart()))
        {
            $marcspec .= "~".$charStart;
            
            if(!is_null($charEnd = $this->getCharEnd()))
            {
                $marcspec .= "-".$charEnd;
            }
            
            return $marcspec;
        }
        
        if(!is_null($_subfields = $this->getSubfields()))
        {
            $marcspec .= implode("",$_subfields);
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
        if(isset($this->charStart) or "LDR" === $this->fieldTag) return false;
        $this->checkIfString($arg);
        for($x = 0; $x < strlen($arg); $x++)
        {
            $this->subfields[$arg[$x]] = $arg[$x];
        }
        return true;
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
            throw new \InvalidArgumentException("Marc spec must be 3 characters at minimum.");
        }
        if(preg_match('/\s/', $spec))
        {
            throw new \InvalidArgumentException('For Field Tag of Marc spec no whitespaces are allowed. But "'.$spec.'" given.');
        }
        // check and set field tag
        $fieldTag = substr($spec, 0, 3);
        $this->setFieldTag($fieldTag);
        
        $dataRef = substr($spec, 3);
        if("" != $dataRef)
        {
            if("~" == substr($spec, 3, 1))
            {
                // check character postion or range
                $charPos = substr($spec, 4);
                if("" != strlen($charPos))
                {
                    if($_charPos = $this->validateCharPos($charPos))
                    {
                        $this->setCharStart((int)$_charPos[0]);
                        if(array_key_exists(1,$_charPos))
                        {
                            $this->setCharEnd((int)$_charPos[1]);
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
                if($_dataRef = $this->validateDataRef($dataRef))
                {
                    
                    $this->addSubfields($_dataRef[0]);
                    if(array_key_exists(1,$_dataRef)) $this->setIndicators($_dataRef[1]);
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
        
        if(!preg_match('/[X0-9]{3,3}|LDR/', $fieldTag))
        {
            throw new \InvalidArgumentException('For Field Tag of Marc spec only digits, "X" or "LDR" is allowed. But "'.$fieldTag.'" given.');
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
        
        if(1 > strlen($charPos))
        {
            throw new \InvalidArgumentException('For character position or range minimum one digit is required. None given.');
        }
        $_charPos = explode("-",$charPos);
        if(2 < count($_charPos))
        {
            throw new \InvalidArgumentException('For character position or range only digits and one "-" is allowed. But "'.$charPos.'" given.');
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
    * @throws \InvalidArgumentException If the argument consits of characters not allowed
    */
    private function validateDataRef($dataFieldRef)
    {
        $this->checkIfString($dataFieldRef);
        
        $_ref = explode("_",$dataFieldRef,2);
        
        if($this->validateSubfields($_ref[0]))
        {
            if(array_key_exists(1,$_ref))
            {
                $this->validateIndicators($_ref[1]);
            }
        }
        return $_ref;
    }
    
    /**
    * validate subfield tags and indicators
    *
    * @access private
    *
    * @param string $dataFieldRef   The specification for subfields and indicators
    *
    * @throws \InvalidArgumentException If the argument consits of characters not allowed
    *
    * @return true
    */
    private function validateSubfields($subfields)
    {
        for($x = 0; $x < strlen($subfields); $x++)
        {
            if(!preg_match("/[a-z0-9!\"#$%&'()*+-.\/:;<=>?]/", $subfields[$x]))
            {
                throw new \InvalidArgumentException('For subfields only digits, lowercase alphabetic characters or one of "!\"#$%&\'()*+-./:;<=>?" are allowed. But "'.$subfields.'" given.');
            }
        }
        return true;
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
            
            if(2 < strlen($indicators))
            {
                throw new \InvalidArgumentException('For indicators only two characters are allowed. "'.strlen($indicators).'" characters given.');

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
    */
    private function clear()
    {
        unset($this->fieldTag,$this->charStart,$this->charEnd,$this->charLength,$this->indicator1,$this->indicator2);
        $this->subfields = array();
    }
}