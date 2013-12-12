<?php

namespace CK\MarcSpec;

class MarcSpec {

   /**
   * @var string field tag
   */
   public $fieldTag;
   /**
   * @var int starting character position
   */
   public $charStart;
   /**
   * @var int ending character position
   */
   public $charEnd;
   /**
   * @var int length of character position range
   */
   public $charLength;
   /**
   * @var array Array of subfield tags
   */
   public $subfields;
   /**
   * @var string indicator 1
   */
   public $indicator1;
   /**
   * @var string indicator 2
   */
   public $indicator2;
   
    public function decode($spec)
    {
        $this->clear();
        $this->checkIfString($spec);
        $this->validate($spec);
    }
    
    /**
    * clear all properties
    */
    private function clear()
    {
        unset($this->fieldTag,$this->charStart,$this->charEnd,$this->charLength,$this->subfields,$this->indicator1,$this->indicator2);
    }
    
    /**
    * validates a MARC spec as string
    */
    public function validate($spec)
    {
        $this->checkIfString($spec);
        $spec = trim($spec);
        
        // check string length
        if(3 > strlen($spec))
        {
            throw new \InvalidArgumentException("Marc spec must be 3 characters at minimum.");
        }
        if(preg_match('/\s/', $spec))
        {
            throw new \InvalidArgumentException('For Field Tag of Marc spec only digits, "X" or "LDR" is allowed. But "'.$spec.'" given.');
        }
        // check field tag
        $fieldTag = substr($spec, 0, 3);
        $this->validateFieldTag($fieldTag);
        
        $deepRef = substr($spec, 3);
        if("" != $deepRef)
        {
            if("~" == substr($spec, 3, 1))
            {
                // check character postion or range
                $charPos = substr($spec, 4);
                if("" != strlen($charPos))
                {
                    $this->validateCharPos($charPos);
                }
                else
                {
                    throw new \InvalidArgumentException('For character position or range minimum one digit is required. None given.');
                }
            }
            else
            {
                $dataFieldRef = $deepRef;
                $this->validateSubfield($dataFieldRef);
            }
        }
    
    }
    
    /**
    * validate a field tag
    */
    private function validateFieldTag($fieldTag)
    {
        $this->checkIfString($fieldTag);
        
        if(!preg_match('/[X0-9]{3,3}|LDR/', $fieldTag))
        {
            throw new \InvalidArgumentException('For Field Tag of Marc spec only digits, "X" or "LDR" is allowed. But "'.$fieldTag.'" given.');
        }
        else
        {
            $this->fieldTag = $fieldTag;
        }
    }
    /**
    * validate a character position or range
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
        else
        {
            $this->charStart = (int)$_charPos[0];
            if(array_key_exists(1,$_charPos))
            {
                $this->charEnd = (int)$_charPos[1];
                $this->charLength = $this->charEnd - $this->charStart + 1;
            }
            else
            {
                $this->charLength = 1;
                $this->charEnd = $this->charStart;
            }
        }
    }
    /**
    * validate subfield tags and indicators
    */
    private function validateSubfield($dataFieldRef)
    {
        $this->checkIfString($dataFieldRef);
        
        $_ref = explode("_",$dataFieldRef,2);
        
        $subfields = $_ref[0];

        for($x = 0; $x < strlen($subfields); $x++)
        {
            if(!preg_match("/[a-z0-9!\"#$%&'()*+-.\/:;<=>?]/", $subfields[$x]))
            {
                throw new \InvalidArgumentException('For subfields only digits, lowercase alphabetic characters or one of "!\"#$%&\'()*+-./:;<=>?" are allowed. But "'.$subfields.'" given.');
            }
            else
            {
                $this->subfields[] = $subfields[$x];
            }
        }
        
        if(array_key_exists(1,$_ref))
        {
            $this->validateIndicators($_ref[1]);
        }
    }
    
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
                else
                {
                    if(0 === $x)
                    {
                        if("_" != $indicators[$x]) $this->indicator1 =  $indicators[$x];
                    }
                    if(1 === $x)
                    {
                        if("_" != $indicators[$x]) $this->indicator2 =  $indicators[$x];
                    }
                }
            }
    }
    
    private function checkIfString($arg)
    {
        if(!is_string($arg)) throw new \InvalidArgumentException("Method decode only accepts string as argument. Given " .gettype($arg).".");
    }
}