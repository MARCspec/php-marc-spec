<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;

use CK\MARCspec\Indicator;
use CK\MARCspec\Exception\InvalidMARCspecException;
use CK\MARCspec\MARCspec;
use CK\MARCspec\SubSpec;


class IndicatorTest extends \PHPUnit_Framework_TestCase
{
    protected $validTests = [];
    protected $invalidTests = [];
    
    protected function setUp()
    {
        if(0 < count($this->validTests)) return;
        $this->validTests[] = json_decode(file_get_contents(__DIR__. '/../' ."vendor/ck/marcspec-test-suite/valid/validIndicators.json"));
        $this->invalidTests[] = json_decode(file_get_contents(__DIR__. '/../' ."vendor/ck/marcspec-test-suite/invalid/invalidIndicators.json"));
    }

    public function indicatorPos($arg)
    {
        $ind = new Indicator($arg);
        return $ind->getPos();
    }

    public function testInvalidFromTestSuite()
    {
        foreach($this->invalidTests as $invalid)
        {
            foreach($invalid->{'tests'} as $test)
            {
                try
                {
                    $this->indicatorPos($test->{'data'});
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
                $pos = $this->indicatorPos($test->{'data'});
                $this->assertTrue($pos === "1" or $pos === "2");
            }
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPosFail(){
        $ind = new Indicator('3');
    }
    /**
     * @expectedException ArgumentCountError
     */
    public function testPosFail2(){
        $ind = new Indicator();
    }

    public function testSetPos(){
        $ind = new Indicator(2);
        $this->assertInstanceOf('CK\MARCspec\IndicatorInterface', $ind);
        $this->assertSame("2", $ind->getPos());
        $this->assertSame('^2', $ind->getBaseSpec());
        $ind = new Indicator('1');
        $this->assertSame("1", $ind->getPos());
        $this->assertSame('^1', $ind->getBaseSpec());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIndicatorFail1(){
        $ind = new Indicator(3);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIndicatorFail2(){
        $ind = new Indicator(['3']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIndicatorFail3(){
        $ind = new Indicator('12');
    }
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testIndicatorFail4(){
        $ind = new Indicator('1{$a}');
    }
    
    public function testSubspecsAndArrayAccessAndToString(){
        $ind = new Indicator('1');
        $Subspec = new SubSpec(new MARCspec('245$b'),'!=',new MARCspec('245$c'));
        $ind['subSpecs'] = $Subspec;
        $this->assertSame('^1{245[0-#]$b[0-#]!=245[0-#]$c[0-#]}',$ind->__toString());
        $ind->addSubSpec($Subspec);
        $this->assertTrue($ind->offsetExists('position'));
        $this->assertTrue($ind->offsetExists('subSpecs'));
    }

        /**
     * @covers CK\MARCspec\Indicator::jsonSerialize
     */
    public function testJson()
    {
        $ind = new Indicator('2');
        $_ind['position'] = "2";
        $this->assertSame(json_encode($_ind), json_encode($ind));
    }


}
