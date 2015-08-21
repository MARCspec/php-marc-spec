<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;

use CK\MARCspec\MARCspec;
use CK\MARCspec\Field;
use CK\MARCspec\Exception\InvalidMARCspecException;
#use CK\MARCspec\Subfield;
#use CK\MARCspec\SubSpec;

class MARCspecTest extends \PHPUnit_Framework_TestCase
{
    protected $validTests = [];
    protected $invalidTests = [];
    
    protected function setUp()
    {
        $valid = [];
        $invalid = [];
        $a = ['valid','invalid'];
        array_walk($a, 
            function($v,$k) use (&$valid,&$invalid)
            {
                foreach (glob(__DIR__. '/../' .'vendor/ck/marcspec-test-suite/'.$v.'/wildCombination_*.json') as $filename)
                {
                    if('valid' == $v)
                    {
                        $valid[] = json_decode(file_get_contents($filename));
                    }
                    else
                    {
                        $invalid[] = json_decode(file_get_contents($filename));
                    }
                }
            }
        );
         $this->validTests = $valid;
         $this->invalidTests = $invalid;
    }
    
    public function marcspec($arg)
    {
        return new MARCspec($arg);
    }
    
    /****
    * invalid data types
    ***/
    
     /**
      * @expectedException RuntimeException
     */
     public function testInvalidArgument01Decode()
     {
             $this->marcspec('24');
     }
    
     /**
      * @expectedException InvalidArgumentException
     */
     public function testInvalidArgument1Decode()
     {
             $this->marcspec((int)'245$a');
     }
     /**
      * @expectedException InvalidArgumentException
     */
     public function testInvalidArgument2Decode()
     {
             $this->marcspec(array('245$a'));
     }
     /**
      * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
     public function testInvalidArgument3Decode()
     {
             $this->marcspec('245/#$a');
     }
    

     /**
      * assert same subfields
     */
     public function testValidMarcSpec1()
     {
          
         $marcSpec = $this->marcspec('245$a-c');
         $this->assertSame(3, count($marcSpec['subfields']));
     }
     /**
      * assert same subfields
     */
     public function testValidMarcSpec2()
     {
         $marcSpec = $this->marcspec('245');
         $marcSpec['subfields'] = '$d-f';
         $this->assertSame(3, count($marcSpec['subfields']));
     }
    
     /**
      * assert same specs
     */
     public function testValidMarcSpec4()
     {
         $marcSpec = $this->marcspec('...[#]/1-3');
         $this->assertSame('...[#]/1-3', "$marcSpec");
     }
    

    /**
     * assert same specs
     */
    public function testValidMarcSpec3()
    {
        $field = new Field('245');
        $marcSpec = MARCspec::setField($field);
        $marcSpec['subfields'] = '$d{$c/#=\.}{?$a}';
        $_subfields = $marcSpec['subfields'];
        $this->assertSame(1, count($_subfields));
        $leftFieldTag = $marcSpec['d'][0]['subSpecs'][0]['leftSubTerm']['field']['tag'];
        $this->assertSame('245',$leftFieldTag);
        $rightSubfieldTag = $marcSpec['d'][0]['subSpecs'][1]['rightSubTerm']['subfields'][0]['tag'];
        $this->assertSame('.',$marcSpec['d'][0]['subSpecs'][0]['rightSubTerm']['comparable']);
    }
    
    /**
     * assert same specs
     */
    public function testValidMarcSpec5()
    {
        $ms = $this->marcspec('245[0]{$a!=$b|300_01$a!~\abc}{\!\=!=\!}$a{$c|!$d}');
        
        // field
        $this->assertSame('245',$ms['field']['tag']);
        $this->assertSame(0,$ms['field']['indexStart']);
        $this->assertSame(2,count($ms['field']['subSpecs']));
        
        // field subspecs
        
        // subspec 00
        $this->assertSame('245',$ms['field']['subSpecs'][0][0]['leftSubTerm']['field']['tag']);
        $this->assertSame(0,$ms['field']['subSpecs'][0][0]['leftSubTerm']['field']['indexStart']);
        $this->assertSame('a',$ms['field']['subSpecs'][0][0]['leftSubTerm']['subfields'][0]['tag']);
        
        $this->assertSame('!=',$ms['field']['subSpecs'][0][0]['operator']);
        
        $this->assertSame('245',$ms['field']['subSpecs'][0][0]['rightSubTerm']['field']['tag']);
        $this->assertSame(0,$ms['field']['subSpecs'][0][0]['rightSubTerm']['field']['indexStart']);
        $this->assertSame('b',$ms['field']['subSpecs'][0][0]['rightSubTerm']['subfields'][0]['tag']);
        
        // subspec 01
        $this->assertSame('300',$ms['field']['subSpecs'][0][1]['leftSubTerm']['field']['tag']);
        $this->assertSame('a',$ms['field']['subSpecs'][0][1]['leftSubTerm']['subfields'][0]['tag']);
        $this->assertSame('0',$ms['field']['subSpecs'][0][1]['leftSubTerm']['field']['indicator1']);
        $this->assertSame('1',$ms['field']['subSpecs'][0][1]['leftSubTerm']['field']['indicator2']);
        
        $this->assertSame('!~',$ms['field']['subSpecs'][0][1]['operator']);
        
        $this->assertSame('abc',$ms['field']['subSpecs'][0][1]['rightSubTerm']['comparable']);
        
        // subspec 1
        $this->assertSame('!=',$ms['field']['subSpecs'][1]['leftSubTerm']['comparable']);
        
        $this->assertSame('!=',$ms['field']['subSpecs'][1]['operator']);
        
        $this->assertSame('!',$ms['field']['subSpecs'][1]['rightSubTerm']['comparable']);

        // subfields
        $this->assertSame('a',$ms['subfields'][0]['tag']);
        
        // subfield subspec 00
        $this->assertSame('245',$ms['a'][0]['subSpecs'][0][0]['leftSubTerm']['field']['tag']);
        $this->assertSame(0,$ms['a'][0]['subSpecs'][0][0]['leftSubTerm']['field']['indexStart']);
        $this->assertSame('a',$ms['a'][0]['subSpecs'][0][0]['leftSubTerm']['subfields'][0]['tag']);
        
        $this->assertSame('?',$ms['a'][0]['subSpecs'][0][0]['operator']);
        
        $this->assertSame('245',$ms['a'][0]['subSpecs'][0][0]['rightSubTerm']['field']['tag']);
        $this->assertSame(0,$ms['a'][0]['subSpecs'][0][0]['rightSubTerm']['field']['indexStart']);
        $this->assertSame('c',$ms['a'][0]['subSpecs'][0][0]['rightSubTerm']['subfields'][0]['tag']);
        
                
        // subfield subspec 01
        $this->assertSame('245',$ms['a'][0]['subSpecs'][0][1]['leftSubTerm']['field']['tag']);
        $this->assertSame(0,$ms['a'][0]['subSpecs'][0][1]['leftSubTerm']['field']['indexStart']);
        $this->assertSame('a',$ms['a'][0]['subSpecs'][0][1]['leftSubTerm']['subfields'][0]['tag']);
        
        $this->assertSame('!',$ms['a'][0]['subSpecs'][0][1]['operator']);
        
        $this->assertSame('245',$ms['a'][0]['subSpecs'][0][1]['rightSubTerm']['field']['tag']);
        $this->assertSame(0,$ms['a'][0]['subSpecs'][0][1]['rightSubTerm']['field']['indexStart']);
        $this->assertSame('d',$ms['a'][0]['subSpecs'][0][1]['rightSubTerm']['subfields'][0]['tag']);

    }
    
    public function testIteration()
    {
        $ms = $this->marcspec('245$a-c{$b|$c}{$e}');
        $count = 0;

        foreach($ms as $key => $value)
        {
            $count++;
        }
        $this->assertSame(2, $count);
        
        $count = 0;
        foreach($ms['subfields'] as $key => $value)
        {
            $count++;
        }
        $this->assertSame(3, $count);
        
       
        foreach($ms['subfields'] as $subfield)
        {
            $count = 0;
            foreach($subfield['subSpecs'] as $subSpec)
            {
                if(is_array($subSpec))
                {
                    $this->assertSame(2, count($subSpec));
                }
                else
                {
                    foreach($subSpec as $key => $prop)
                    {
                        $this->assertTrue(in_array($key,["leftSubTerm","operator","rightSubTerm"]));
                    }
                }
                
                $count++;
            }
            $this->assertSame(2, $count);
        }
        
        
    }
    
    public function testOffsets()
    {
        $ms = $this->marcspec('LDR/0-3');
        $this->assertTrue($ms['field']->offsetExists('charLength'));
        
        $ms = $this->marcspec('LDR/0-#');
        $this->assertFalse($ms['field']->offsetExists('charLength'));
        
        $ms = $this->marcspec('245$a/0-3');
        $this->assertTrue($ms['a'][0]->offsetExists('charLength'));
        
        $ms = $this->marcspec('245$a/#-3');
        $this->assertTrue($ms['a'][0]->offsetExists('charLength'));
        
        $ms = $this->marcspec('245$a/0-#');
        $this->assertFalse($ms['a'][0]->offsetExists('charLength'));
    }

    public function testInvalidFromTestSuite()
    {
        foreach($this->invalidTests as $invalid)
        {
            foreach($invalid->{'tests'} as $test)
            {
                try
                {
                    new MARCspec($test->{'data'});
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
                    new MARCspec($test->{'data'});
                }
                catch(\Exception $e)
                {
                    $this->fail('An unexpected exception has been raised for '.$test->{'data'}.': '.$e->getMessage());
                }
            }
        }
    }
}
