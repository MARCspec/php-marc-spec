<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MarcSpec\Test;



ini_set('include_path', '/usr/share/pear');
require_once "autoload.php";
require_once "/home/sbb-zdb2/htdocs/php/phpunit-3.7/PHPUnit/Autoload.php";

use CK\MarcSpec\MarcSpec;


class MarcSpecTest extends \PHPUnit_Framework_TestCase
{
    public function decoder($arg)
    {
        $ms = new MarcSpec;
        return $ms->decode($arg);
    }
    
    public function marcspec()
    {
        return new MarcSpec;
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument1Decode()
    {
            $this->decoder((int)'245a');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument2Decode()
    {
            $this->decoder(array('245a'));
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec1()
    {
            $this->decoder(' 24 ');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec2()
    {
            $this->decoder('24/');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec3()
    {
            $this->decoder('24x');
    }
    
    /**
     * assert same field tag
     */
    public function testValidMarcSpec1()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->decode('LDR');
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            
            $marcSpec->decode('245');
            $this->assertSame('245', $marcSpec->getFieldTag());
            
            $marcSpec->decode('XXX');
            $this->assertSame('XXX', $marcSpec->getFieldTag());
            
            $marcSpec->decode('245abc');
            $this->assertSame('245', $marcSpec->getFieldTag());
            $this->assertSame(array('a'=>'a','b'=>'b','c'=>'c'), $marcSpec->getSubfields());
            
            $marcSpec->decode('245!"#$%&\'()*+-./:;<=>?');
            $this->assertSame(
                array(
                    "!"=>"!",
                    "\""=>"\"",
                    "#"=>"#",
                    "$"=>"$",
                    "%"=>"%",
                    "&"=>"&",
                    "'"=>"'",
                    "("=>"(",
                    ")"=>")",
                    "*"=>"*",
                    "+"=>"+",
                    "-"=>"-",
                    "."=>".",
                    "/"=>"/",
                    ":"=>":",
                    ";"=>";",
                    "<"=>"<",
                    "="=>"=",
                    ">"=>">",
                    "?"=>"?"
                ), $marcSpec->getSubfields());
            
            $marcSpec->decode('245ab_1a');
            $this->assertSame('1', $marcSpec->getIndicator1());
            $this->assertSame('a', $marcSpec->getIndicator2());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec4()
    {
            $this->decoder('007~');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec5()
    {
            $this->decoder('007~1-2-');
    }
    
    /**
     * test character position and range
     */
    public function testValidMarcSpec2()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->decode('LDR~0-3');
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            $this->assertSame(0, $marcSpec->getCharStart());
            $this->assertSame(3, $marcSpec->getCharEnd());
            $this->assertSame(4, $marcSpec->getCharLength());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec6()
    {
            $this->decoder('245aX');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec7()
    {
            $this->decoder('245ab_1+');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec8()
    {
            $this->decoder('245ab_123');
    }
    
    /**
     * test character position and range
     */
    public function testSetAndGet()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->setFieldTag('LDR');
            $marcSpec->setCharStart(0);
            $marcSpec->setCharEnd(3);
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            $this->assertSame(0, $marcSpec->getCharStart());
            $this->assertSame(3, $marcSpec->getCharEnd());
            $this->assertSame(4, $marcSpec->getCharLength());
            
            $marcSpec = $this->marcspec();
            $marcSpec->setFieldTag('245');
            $marcSpec->addSubfields('abc');
            $marcSpec->setIndicator1('x');
            $marcSpec->setIndicator2('0');
            $this->assertSame('245', $marcSpec->getFieldTag());
            $this->assertSame('x', $marcSpec->getIndicator1());
            $this->assertSame('0', $marcSpec->getIndicator2());
            $this->assertSame(array('a'=>'a','b'=>'b','c'=>'c'), $marcSpec->getSubfields());
            
    }
    
    /**
     * test encoding
     */
    public function testEncode()
    {
        $marcSpec = new MarcSpec("245");
        $this->assertSame('245', $marcSpec->encode());
        
        $marcSpec = new MarcSpec("245a");
        $this->assertSame('245a', $marcSpec->encode());
        
        $marcSpec = new MarcSpec("245_1");
        $this->assertSame('245_1', $marcSpec->encode());
        
        $marcSpec = new MarcSpec("245__0");
        $this->assertSame('245__0', $marcSpec->encode());
        
        $marcSpec = new MarcSpec("245_1_");
        $this->assertSame('245_1', $marcSpec->encode());
        
        $marcSpec = new MarcSpec("007~1");
        $this->assertSame('007~1-1', $marcSpec->encode());
        
        $marcSpec = new MarcSpec("007~1-3");
        $this->assertSame('007~1-3', $marcSpec->encode());
    }

    /**
     * test validity
     */
    public function testValidity()
    {
        $marcSpec = new MarcSpec;
        $this->assertTrue($marcSpec->validate('245'));
        $this->assertTrue($marcSpec->validate('245ab'));
        $this->assertTrue($marcSpec->validate('XXXab'));
        $this->assertTrue($marcSpec->validate('245ab_1'));
        $this->assertTrue($marcSpec->validate('245ab__0'));
        $this->assertTrue($marcSpec->validate('245ab_10'));
        $this->assertTrue($marcSpec->validate('245ab_1_'));
    }
    
    /**
     * test invalidity
     * @expectedException InvalidArgumentException
     */
    public function testInValidity()
    {
        $marcSpec = new MarcSpec;
        $this->assertTrue($marcSpec->validate('24'));
        $this->assertTrue($marcSpec->validate('LXR'));
        $this->assertTrue($marcSpec->validate('245ab_123'));
        $this->assertTrue($marcSpec->validate('245ab__.'));
        $this->assertTrue($marcSpec->validate('004ab~1'));
        $this->assertTrue($marcSpec->validate('004~-1'));
    }
}