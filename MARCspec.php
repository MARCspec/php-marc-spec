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
* Class to decode, validate and encode MARC spec as string.
* For Specification of MARC spec as string see
* <http://cklee.github.io/marc-spec/marc-spec.html>
*/
class MARCspec implements MARCspecInterface, \JsonSerializable, \ArrayAccess, \IteratorAggregate
{

    /**
    * @var Field The field object
    */
    private $field;

    /**
    * @var array Array of subfields
    */
    private $subfields = [];

    /**
     * @constant string Regex for leftSubTerm
     */
    const LEFTSUBTERM = '^(?<leftsubterm>(?:\\\(?:(?<=\\\)[\!\=\~\?]|[^\!\=\~\?])+)|(?:(?<=\$)[\!\=\~\?]|[^\!\=\~\?])+)?';
    
    /**
     * @constant string Regex for operator
     */
    const OPERATOR = '(?<operator>\!\~|\!\=|\=|\~|\!|\?)';
    

    /**
    * {@inheritdoc}
    * 
    * @throws InvalidMARCspecException
    */ 
    public function __construct($spec)
    {
        if($spec instanceof FieldInterface)
        {
            $this->field = $spec;
        }
        else
        {
            $this->checkIfString($spec);
            $spec = trim($spec);
            $specLength = strlen($spec);
            // check string length
            if(3 > $specLength)
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::MS.
                    InvalidMARCspecException::LENGTH.
                    InvalidMARCspecException::MINIMUM3,
                    $spec
                );
            }
            if(preg_match('/\s/', $spec))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::MS.
                    InvalidMARCspecException::SPACE,
                    $spec
                );
            }
            
            /**
             * $specMatches[0] => whole spec 
             * $specMatches[1] => fieldspec
             * $specMatches[2] => rest
             */ 
            if(0 === preg_match('/^([^{$]*)(.*)/',$spec,$specMatches))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::MS.
                    InvalidMARCspecException::UNKNOWN,
                    $spec
                );
            }
            
            
            // creates a fieldspec
            if(empty($specMatches[1]))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::MS.
                    InvalidMARCspecException::MISSINGFIELD,
                    $spec
                );
            }
            $this->field = new Field($specMatches[1]);

            // process rest
            if(!empty($specMatches[2]))
            {
                $this->createInstances($this->parseDataRef($specMatches[2]));
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public static function setField(FieldInterface $field)
    {
        return new MARCspec($field);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSubfields()
    {
        return (0 < count($this->subfields)) ? $this->subfields : null;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @throws \UnexpectedValueException if argument length is higher than 1
     */
    public function getSubfield($arg)
    {
        if(1 < strlen($arg))
        {
            throw new \UnexpectedValueException('Method only allows argument to be 1 
                character long. Got '. strlen($arg)
            );
        }
        $_subfields = null;
        if(0 < count($this->subfields))
        {
            foreach($this->subfields as $subfield)
            {
                if( $subfield->getTag() == $arg ) $_subfields[] = $subfield; 
            }
            return $_subfields;
        }
        
        return null;
    }
    /**
     * {@inheritdoc}
     * 
     * @throws InvalidMARCspecException
     * 
     */
    public function addSubfields($subfields)
    {
        if($subfields instanceof SubfieldInterface)
        {
            $this->subfields[] = $subfields;
        }
        else
        {
            $this->checkIfString($subfields);
            if(2 > strlen($subfields))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidMARCspecException::LENGTH.
                    InvalidSubfieldspecException::MINIMUM2,
                    $subfields
                );
            }
            if('$' !== $subfields[0])
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SF.
                    InvalidSubfieldspecException::PREFIX,
                    $subfields
                );
            }
            $this->createInstances($this->parseDataRef($subfields));
        }
    }
    

    
    /**
    * Parses subfield ranges into single subfields
    *
    * @internal
    *
    * @param string $arg The assumed subfield range
    * 
    * @return array $_range[string] An array of subfield specs
    * 
    * @throws InvalidMARCspecException
    */
    private function handleSubfieldRanges($arg)
    {
        if(strlen($arg) < 3) 
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::LENGTH3,
                $arg
            );
        }
        /**
        * $_arg[0] = whole match
        * $_arg[1] = subfield range
        * $_arg[2] = subfield index
        * $_arg[3] = subfield charspec
        */
        preg_match('/^([0-9a-zA-Z-]{3,3})(?:(\[[0-9#-]{1,3}\])?(\/[0-9#-]{1,3})?)$/', $arg, $_arg);
        
        if(preg_match('/[a-z]/', $_arg[1][0]) && !preg_match('/[a-z]/', $_arg[1][2]))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }
        if(preg_match('/[A-Z]/', $_arg[1][0]) && !preg_match('/[A-Z]/', $_arg[1][2]))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }
        if(preg_match('/[0-9]/', $_arg[1][0]) && !preg_match('/[0-9]/', $_arg[1][2]))
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }
        if($_arg[1][0] > $_arg[1][2])
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::RANGE,
                $arg
            );
        }

        foreach(range($_arg[1][0],$_arg[1][2]) as $sfStep)
        {
             $range = (array_key_exists('2', $_arg) && strlen($_arg[2])) ? $sfStep.$_arg[2] : $sfStep;
             $range = (array_key_exists('3', $_arg) && strlen($_arg[3])) ? $range.$_arg[3] : $range;
             $_range[] = '$'.$range;
        }
        return $_range;
    }
    

    


    /**
     * checks if argument is a string
     * 
     * @internal
     * 
     * @param string $arg The argument to check
     * 
     * @throws \InvalidArgumentException if the argument is not a string
     */
    private function checkIfString($arg)
    {
        if(!is_string($arg))
        {
            throw new \InvalidArgumentException("Method only accepts string as argument. " .
                gettype($arg)." given."
            );
        }
    }
    

    /**
    * Detects and creates subfields and subspecs
    *
    * @param string $arg string of subfieldspecs and/or subspecs
    * 
    * @return array $_detected Associative array of instances of Subfield and SubSpec
    * 
    * @throws InvalidMARCspecException
    */
    public static function parseDataRef($arg)
    {
        $open = 0;
        $close = 0;
        $_nocount = ['$','\\'];
        $_detected = [];
        $subfieldCount = 0;
        $subSpecCount = 0;
        $checkPrevious = function($x) use ($arg,$_nocount)
        {
            $start = $x;
            $count = true;
            for($x ; $x > 0; $x--)
            {
                if(!in_array($arg[$x-1],$_nocount)) 
                {
                    $check = $start - $x;
                    if ($check % 2 != 0) {
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
                else
                {
                    $count = false;
                }
            }
            return $count;
        };
        for($i = 0;$i < strlen($arg);$i++)
        {
            if(0 < $i) // not first char
            {
                if('$' == $arg[$i] && $checkPrevious($i) && ($open === $close))
                {
                    $subfieldCount++;
                }
            }
        
            if($open === $close)
            {
                if('{' == $arg[$i] && $checkPrevious($i))
                {
                    $open++;
                }
                if('}' == $arg[$i] && $checkPrevious($i))
                {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::MS.
                        InvalidMARCspecException::BRACKET,
                        $arg
                    );
                }
                
                if($open !== $close)
                {
                    if(array_key_exists('subspec',$_detected))
                    {
                        if(array_key_exists($subSpecCount,$_detected[$subfieldCount]['subspec']))
                        {
                            $subspec = $_detected[$subfieldCount]['subspec'][$subSpecCount].$arg[$i];
                            $_detected[$subfieldCount]['subspec'][$subSpecCount] = $subspec;
                        }
                        else
                        {
                            $_detected[$subfieldCount]['subspec'][] = $arg[$i];
                        }
                    }
                    else
                    {
                        $_detected[$subfieldCount]['subspec'][] = $arg[$i];
                    }
                }
                else
                {
                    $subSpecCount = 0;
                    if(array_key_exists($subfieldCount,$_detected))
                    {
                        $spec = $_detected[$subfieldCount]['subfield'].$arg[$i];
                        $_detected[$subfieldCount]['subfield'] = $spec;
                    }
                    else
                    {
                        $_detected[] = ['subfield'=>$arg[$i]];
                    }
                }
            }
            else // open != close
            {
                
                if('{' == $arg[$i] && $checkPrevious($i))
                {
                    $open++;
                }
                if('}' == $arg[$i] && $checkPrevious($i))
                {
                    $close++;
                }
                
                $subspec = $_detected[$subfieldCount]['subspec'][$subSpecCount].$arg[$i];
                $_detected[$subfieldCount]['subspec'][$subSpecCount] = $subspec;
                
                if($open === $close) $subSpecCount++;
                
            }
        }
        if($open !== $close)
        {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::MS.
                InvalidMARCspecException::BRACKET." ".
                InvalidMARCspecException::HINTESCAPED,
                $arg
            );
        }
        return $_detected;
    }
    
    /**
     * Creates instances of Subfield and Subspec
     * 
     * @param array $_detected Associative array of subfields and subspecs
     */ 
    private function createInstances($_detected)
    {
        foreach($_detected as $key => $_dataRef)
        {
            $_subfields = [];
            if(array_key_exists('subfield',$_dataRef))
            {
                if(!is_null($this->field->getCharStart()))
                {
                    throw new InvalidMARCspecException(
                        InvalidMARCspecException::MS.
                        InvalidMARCspecException::CHARANDSF,
                        $this->field->__toString()
                    );
                }
                if(2 == strpos($_dataRef['subfield'],'-')) // assuming subfield range
                {
                    $_subfields = $this->handleSubfieldRanges(substr($_dataRef['subfield'],1));
                }
                else
                {
                    $_subfields[] = $_dataRef['subfield'];
                }
                foreach($_subfields as $subfield)
                {
                    $Subfield = new Subfield($subfield);
                    
                    $this->subfields[] = $Subfield;
                    
                    if(array_key_exists('subspec',$_dataRef)) 
                    {
                        foreach($_dataRef['subspec'] as $subspec)
                        {
                            $_Subspecs = $this->createSubSpec($subspec,$Subfield);
                            $Subfield->addSubSpec($_Subspecs);
                        }
                    }
                }
            }
            else
            {
                foreach($_dataRef['subspec'] as $subKey => $subspec)
                {
                    $Subspec = $this->createSubSpec($subspec);
                    $this->field->addSubSpec($Subspec);
                }
            }
        }
    }
    
    /**
     * Creates SubSpecs
     * 
     * @internal
     *
     * @param string $assumedSubspecs A string with assumed subSpecs
     * 
     * @return SubSpecInterface|$_subSpec[SubSpecInterface] Instance of SubSpecInterface
     * or numeric array of instances of SubSpecInterface
     * 
     * @throws InvalidMARCspecException
     */
    private function createSubSpec($assumedSubspecs,$Subfield=null)
    {
        $context = $this->field->getBaseSpec();
        if(!is_null($Subfield))
        {
            $context .= $Subfield->getBaseSpec();
        }
        $_nocount = ['$','\\'];
        $_operators = ['?','!','~','='];
        $specLength = strlen($assumedSubspecs);

        $_subTermSets = preg_split('/(?<!\\\\)\|/', substr($assumedSubspecs,1,$specLength-2));
        
        foreach($_subTermSets as $key => $subTermSet)
        {
            if(preg_match('/(?<![\\\\\$])[\{\}]/',$subTermSet,$_error, PREG_OFFSET_CAPTURE))
            {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::MS.
                    InvalidMARCspecException::ESCAPE,
                    $assumedSubspecs
                );
            }
            $_subTermSet = [];
            $_subTerms = $this->subTermsToArray($subTermSet);
            
            foreach([
                    'leftSubTerm'=>$_subTerms['leftSubTerm'],
                    'rightSubTerm'=>$_subTerms['rightSubTerm']
                ] as $subTermKey => $subTerm)
            {
                if(!is_null($subTerm))
                {
                    if('\\' == $subTerm[0]) // is a comparisonString
                    {
                        $_subTermSet[$subTermKey] = new ComparisonString(substr($subTerm,1));
                    }
                    else
                    {
                        switch($subTerm[0]) 
                        {
                            case '[':
                            case '/':
                            case '_':
                            case '$':
                                if(is_null($context))
                                {
                                    throw new InvalidMARCspecException(
                                        InvalidMARCspecException::SS.
                                        InvalidMARCspecException::MISSINGFIELD,
                                        $assumedSubspecs
                                    );
                                }
                                if('_' == $subTerm[0])
                                {
                                    $refPos = strpos($context,$subTerm[0]);
                                }
                                else
                                {
                                    $refPos = strrpos($context,$subTerm[0]);
                                }
                                if($refPos)
                                {
                                    $_subTermSet[$subTermKey] = new MARCspec(substr($context,0,$refPos).$subTerm);
                                }
                                else
                                {
                                    $_subTermSet[$subTermKey] = new MARCspec($context.$subTerm);
                                }
                            break;
                            default: $_subTermSet[$subTermKey] = new MARCspec($subTerm);
                        }
                    }
                }
                else
                {
                    $_subTermSet[$subTermKey] = new MARCspec($context);
                }
            }
            $_subSpec[$key] = new SubSpec($_subTermSet['leftSubTerm'],
                $_subTerms['operator'],
                $_subTermSet['rightSubTerm']);
        }
        return (1 < count($_subSpec)) ? $_subSpec : $_subSpec[0];
    }
    
    private function subTermsToArray($subTermSet)
    {
        $_subTermSet = [];
        if(preg_match_all('/(?:'.self::LEFTSUBTERM.self::OPERATOR.')?(?<rightsubterm>.+)/',$subTermSet,$_subTermMatches,PREG_SET_ORDER))
        {
            if(array_key_exists('leftsubterm',$_subTermMatches[0]) 
                && !empty($_subTermMatches[0]['leftsubterm'])
            )
            {
                $_subTermSet['leftSubTerm'] = $_subTermMatches[0]['leftsubterm'];
            }
            else
            {
                $_subTermSet['leftSubTerm'] = null;
            }
            
            if(array_key_exists('rightsubterm',$_subTermMatches[0]) 
                && !empty($_subTermMatches[0]['rightsubterm'])
            )
            {
                $_subTermSet['rightSubTerm'] = $_subTermMatches[0]['rightsubterm'];
            }
            else
            {
                throw new InvalidMARCspecException(
                InvalidMARCspecException::SS.
                InvalidMARCspecException::MISSINGRIGHT,
                $subTermSet
                );
            }
            
            if(array_key_exists('operator',$_subTermMatches[0]) 
                && !empty($_subTermMatches[0]['operator'])
            )
            {
                $_subTermSet['operator'] = $_subTermMatches[0]['operator'];
            }
            else
            {
                $_subTermSet['operator'] = '?';
            }
            return $_subTermSet;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() 
    {
        $_marcSpec['field'] = $this->field->jsonSerialize();
        
        foreach($this->subfields as $subfield)
        {
            $_marcSpec['subfields'][] = $subfield->jsonSerialize();
        }
        return $_marcSpec;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $marcspec = "$this->field";
        foreach($this->subfields as $subfield)
        {
            $marcspec .= "$subfield";
        }
        return $marcspec;
    }
    
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key field|subfield
     */ 
    public function offsetExists($offset)
    {
        switch($offset)
        {
            case 'field': return isset($this->field);
            break;
            case 'subfields': return (0 < count($this->subfields)) ? true : false;
            break;
            default: return false;
        }
    }
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key field|subfield
     */ 
    public function offsetGet($offset)
    {
        switch($offset)
        {
            case 'field': return $this->getField();
            break;
            case 'subfields': return $this->getSubfields();
            break;
            default: return $this->getSubfield($offset);
        }
    }
    /**
     * Access object like an associative array
     * 
     * @api
     * 
     * @param string $offset Key subfield
     */ 
    public function offsetSet($offset,$value)
    {
        switch($offset)
        {
            case 'subfields': $this->addSubfields($value);
            break;
            default: throw new \UnexpectedValueException("Offset $offset cannot be set.");
        }
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
    
    public function getIterator()
    {
        return new SpecIterator(["field" => $this->field, "subfields" => $this->subfields]);
    }
} // EOC
