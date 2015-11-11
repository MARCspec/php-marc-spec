<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;

use CK\MARCspec\Subfield;
use CK\MARCspec\SubSpec;
use CK\MARCspec\MARCspec;
use CK\MARCspec\Exception\InvalidMARCspecException;

class SubfieldTest extends \PHPUnit_Framework_TestCase
{
    protected $validTests = [];
    protected $invalidTests = [];
    
    protected function setUp()
    {
        if(0 < count($this->validTests)) return;
        $this->validTests[] = json_decode(file_get_contents(__DIR__. '/../' ."vendor/ck/marcspec-test-suite/valid/validSubfieldTag.json"));
        $this->invalidTests[] = json_decode(file_get_contents(__DIR__. '/../' ."vendor/ck/marcspec-test-suite/invalid/invalidSubfieldTag.json"));
    }
    
    public function subfieldspec($arg)
    {
        return new Subfield($arg);
    }
    
    /****
    * invalid data types
    ***/
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument1Decode()
    {
        $this->subfieldspec((int)'$a');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument2Decode()
    {
        $this->subfieldspec(array('$a'));
    }
    
    /****
    * invalid subfield tags
    ***/
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec1()
    {
        $this->subfieldspec(' $a ');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec11()
    {
            $this->subfieldspec('$a/');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec12()
    {
            $this->subfieldspec('$a$b');
    }
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec13()
    {
            $this->subfieldspec('|');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec14()
    {
            $this->subfieldspec('$a/1-2-');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec15()
    {
            $this->subfieldspec('$|');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec16()
    {
            $this->subfieldspec('$a/-2');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec17()
    {
            $this->subfieldspec('$a[-2]');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec18()
    {
            $this->subfieldspec('$a[1-2-]');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec19()
    {
            $this->subfieldspec('$a[1-2');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec110()
    {
            $this->subfieldspec('$a/1-X');
    }    
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec111()
    {
            $this->subfieldspec('$a/#-');
    }

    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec112()
    {
            $this->subfieldspec('$a[0-2a]');
    }
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec113()
    {
        $this->subfieldspec('$[1-]');
    }
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidSubfieldSpec114()
    {
        $this->subfieldspec('$a{$b}');
    }

    
    /**
     * assert same properties
     */
    public function testValidSubfieldSpec001()
    {
        $Subfield = $this->subfieldspec('$a');
        $this->assertSame('a', $Subfield->getTag());
    }
    
    /**
     * assert same properties
     */
    public function testValidSubfieldSpec01()
    {
        $Subfield = $this->subfieldspec(null);
        $Subfield->setTag('a');
        $this->assertSame('a', $Subfield->getTag());
        $Subspec = new SubSpec(new MARCspec('245$b'),'!=',new MARCspec('245$c'));
        $Subfield->addSubSpec($Subspec);
        $this->assertSame(1, count($Subfield->getSubSpecs()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidSubSpecAdd1()
    {
        $Subfield = $this->subfieldspec('a');
        $Subspec = new SubSpec('245$b','!','245$b');
        $Subfield->addSubSpec($Subspec);
    }

    /**
     * assert same subfield tag
     */
    public function testValidSubfieldSpec1()
    {
            $Subfield = $this->subfieldspec('$a');
            $this->assertSame('a', $Subfield->getTag());
            
            $Subfield = $this->subfieldspec('$a[1]');
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame(1, $Subfield->getIndexStart());
            
            $Subfield = $this->subfieldspec('$a[1-3]');
            $this->assertSame(1, $Subfield->getIndexStart());
            $this->assertSame(3, $Subfield->getIndexEnd());            
            
            $Subfield = $this->subfieldspec('$a[1-#]');
            $this->assertSame(1, $Subfield->getIndexStart());
            $this->assertSame('#', $Subfield->getIndexEnd());            
            
            $Subfield = $this->subfieldspec('$a[#-3]');
            $this->assertSame('#', $Subfield->getIndexStart());
            $this->assertSame(3, $Subfield->getIndexEnd());
    }
    

    
    /**
     * test character position and range
     */
    public function testValidSubfieldSpec2()
    {
            $Subfield = $this->subfieldspec('$a/0-3');
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame(0, $Subfield->getCharStart());
            $this->assertSame(3, $Subfield->getCharEnd());
            $this->assertSame(4, $Subfield->getCharLength());
            
            $Subfield = $this->subfieldspec('$a/0-#');
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame(0, $Subfield->getCharStart());
            $this->assertSame('#', $Subfield->getCharEnd());
            $this->assertSame(null, $Subfield->getCharLength());            
            
            $Subfield = $this->subfieldspec('$a/#-4');
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame('#', $Subfield->getCharStart());
            $this->assertSame(4, $Subfield->getCharEnd());
            $this->assertSame(5, $Subfield->getCharLength());
            
    }

        
     /**
     * test character range
     */
    public function testValidSubfieldSpec22()
    {
            $Subfield = $this->subfieldspec('$a/#');
            $this->assertSame(1, $Subfield->getCharLength());
            $Subfield = $this->subfieldspec('$a/#-#');
            $this->assertSame(1, $Subfield->getCharLength());
            $Subfield = $this->subfieldspec('$a/#-0');
            $this->assertSame(1, $Subfield->getCharLength());
            $Subfield = $this->subfieldspec('$a/#-1');
            $this->assertSame(2, $Subfield->getCharLength());
            $Subfield = $this->subfieldspec('$a/0-#');
            $this->assertSame(0, $Subfield->getCharStart());
            $this->assertSame("#", $Subfield->getCharEnd());
            $this->assertSame(null, $Subfield->getCharLength());
    }

    

    /**
     * test character position and range
     */
    public function testSetAndGetChar()
    {
            $Subfield = $this->subfieldspec('$a');
            $Subfield->setCharStartEnd('0','3');
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame(0, $Subfield->getCharStart());
            $this->assertSame(3, $Subfield->getCharEnd());
            $this->assertSame(4, $Subfield->getCharLength());
            
            $Subfield = $this->subfieldspec('$a');
            $Subfield->setCharStartEnd("#",3);
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame("#", $Subfield->getCharStart());
            $this->assertSame(3, $Subfield->getCharEnd());
            $this->assertSame(4, $Subfield->getCharLength());
            
            $Subfield = $this->subfieldspec('$a');
            $Subfield->setCharStartEnd(0,4);
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame(0, $Subfield->getCharStart());
            $this->assertSame(4, $Subfield->getCharEnd());
            $this->assertSame(5, $Subfield->getCharLength());
                        
            $Subfield = $this->subfieldspec('$a');
            $Subfield->setCharStartLength("#",4);
            $this->assertSame('a', $Subfield->getTag());
            $this->assertSame("#", $Subfield->getCharStart());
            $this->assertSame(3, $Subfield->getCharEnd());
            $this->assertSame(4, $Subfield->getCharLength());
    }

    /**
     * test index position and range
     */
    public function testSetAndGetIndex()
    {
        $Subfield = $this->subfieldspec('$a');
        $Subfield->setIndexStartEnd('0','3');
        $this->assertSame('a', $Subfield->getTag());
        $this->assertSame(0, $Subfield->getIndexStart());
        $this->assertSame(3, $Subfield->getIndexEnd());
        $this->assertSame(4, $Subfield->getIndexLength());
        
        $Subfield = $this->subfieldspec('$a');
        $Subfield->setIndexStartEnd("#",3);
        $this->assertSame('a', $Subfield->getTag());
        $this->assertSame("#", $Subfield->getIndexStart());
        $this->assertSame(3, $Subfield->getIndexEnd());
        $this->assertSame(4, $Subfield->getIndexLength());
        
        $Subfield = $this->subfieldspec('$a');
        $Subfield->setIndexStartEnd(0,4);
        $this->assertSame('a', $Subfield->getTag());
        $this->assertSame(0, $Subfield->getIndexStart());
        $this->assertSame(4, $Subfield->getIndexEnd());
        $this->assertSame(5, $Subfield->getIndexLength());
        
        $Subfield = $this->subfieldspec('$a');
        $Subfield->setIndexStartLength("#",4);
        $this->assertSame('a', $Subfield->getTag());
        $this->assertSame("#", $Subfield->getIndexStart());
        $this->assertSame(3, $Subfield->getIndexEnd());
        $this->assertSame(4, $Subfield->getIndexLength());
    }
    /**
     * test encoding
     */
    public function testEncode()
    {

        $Subfield = $this->subfieldspec('$a');
        $this->assertSame('$a[0-#]', "$Subfield");
        
        $Subfield = $this->subfieldspec('$a/1');
        $this->assertSame('$a[0-#]/1',"$Subfield");
        $this->assertSame(1, $Subfield->getCharLength());
        
        $Subfield = $this->subfieldspec('$a/1-3');
        $this->assertSame('$a[0-#]/1-3',"$Subfield");
        $this->assertSame(3, $Subfield->getCharLength());
        
        $Subfield = $this->subfieldspec('$a[1]');
        $this->assertSame('$a[1]',"$Subfield");
        
        $Subfield = $this->subfieldspec('$a[1-3]');
        $this->assertSame('$a[1-3]',"$Subfield");
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testOffsetUnset()
    {
        $Subfield = $this->subfieldspec('$a');
        unset($Subfield['tag']);
    }
    
    public function testInvalidFromTestSuite()
    {
        foreach($this->invalidTests as $invalid)
        {
            foreach($invalid->{'tests'} as $test)
            {
                try
                {
                    new Subfield($test->{'data'});
                }
                catch(\Exception $e)
                {
                    continue;
                }
                $this->fail('An expected exception has not been raised for '.$test->{'data'});
            }
        }
    }
    
    public function testValidFromTestSuite()
    {
        foreach($this->validTests as $valid)
        {
            foreach($valid->{'tests'} as $test)
            {
                try
                {
                    new Subfield($test->{'data'});
                }
                catch(\Exception $e)
                {
                    $this->fail('An unexpected exception has been raised for '.$test->{'data'}.': '.$e->getMessage());
                }
            }
        }
    }
}
