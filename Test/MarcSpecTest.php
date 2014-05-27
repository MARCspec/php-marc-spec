<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;
ini_set('include_path', '/usr/share/pear');
require_once "autoload.php";
require_once "/home/sbb-zdb2/htdocs/php/phpunit-3.7/PHPUnit/Autoload.php";

use CK\MARCspec\MARCspec;

class MARCspecTest extends \PHPUnit_Framework_TestCase
{
    public function decoder($arg)
    {
        $ms = new MARCspec;
        return $ms->decode($arg);
    }
    
    public function marcspec()
    {
        return new MARCspec;
    }
    
    /****
    * invalid data types
    ***/
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument1Decode()
    {
            $this->decoder((int)'245$a');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgument2Decode()
    {
            $this->decoder(array('245$a'));
    }
    
    /****
    * invalid field tags
    ***/
    
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
    public function testInvalidMarcSpec11()
    {
            $this->decoder('24/');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec12()
    {
            $this->decoder('2Xx');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec13()
    {
            $this->decoder('007/');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec14()
    {
            $this->decoder('007/1-2-');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec15()
    {
            $this->decoder('24#');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec16()
    {
            $this->decoder('007/-2');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec17()
    {
            $this->decoder('245[-2]');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec18()
    {
            $this->decoder('245[1-2-]');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec19()
    {
            $this->decoder('245[1-2');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec110()
    {
            $this->decoder('007/1-X');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec111()
    {
            $this->decoder('007/#-');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec112()
    {
            $this->decoder('245[0-2a]');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec113()
    {
        $this->decoder('300$a[1-]');
    }
    
    /****
    * invalid subfields
    ***/
    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec20()
    {
            $this->decoder('245$aX');
    }    
    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec21()
    {
            $this->decoder('245a');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec22()
    {
            $this->decoder('245$|');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec23()
    {
            $this->decoder('245$|');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec24()
    {
            $this->decoder('245$ab');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec25()
    {
            $this->decoder('245$a[]');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec26()
    {
            $this->decoder('245$a[-1]');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec27()
    {
            $this->decoder('245$a-b[1]');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec28()
    {
            $this->decoder('245$a-');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec29()
    {
            $this->decoder('245$a-9');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec210()
    {
            $this->decoder('245$-9');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec211()
    {
            $this->decoder('245$a[1-2-3]');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec212()
    {
            $this->decoder('245$a[1-2x]');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec213()
    {
            $this->decoder('245a-c');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec214()
    {
            $this->decoder('245$');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec215()
    {
            $this->decoder('245/1$a');
    }
  
    
    /****
    * invalid indicators
    ***/
    
    
    
    
    
    
    
    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec30()
    {
            $this->decoder('245$a$b_1+');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec31()
    {
            $this->decoder('245$a$b_123');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec32()
    {
            $this->decoder('245$a$b_$');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec33()
    {
            $this->decoder('245$a$b_1|');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec34()
    {
            $this->decoder('245$a$b_10_');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec35()
    {
            $this->decoder('245$a$b___');
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec36()
    {
            $this->decoder('245$a$b-_1');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec37()
    {
            $this->decoder('245_1+$a$b');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec38()
    {
            $this->decoder('245_123$a$b');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec39()
    {
            $this->decoder('245_$$a$b');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec310()
    {
            $this->decoder('245_1|$a$b');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec311()
    {
            $this->decoder('245_10_$a$b');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec312()
    {
            $this->decoder('245___$a$b');
    }    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMarcSpec313()
    {
            $this->decoder('245_1$a$b-');
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
            
            $marcSpec->decode('...');
            $this->assertSame('...', $marcSpec->getFieldTag());
            
            $marcSpec->decode('245[1]');
            $this->assertSame('245', $marcSpec->getFieldTag());
            $this->assertSame(1, $marcSpec->getFieldIndexStart());
            
            $marcSpec->decode('245[1-3]$a');
            $this->assertSame('245', $marcSpec->getFieldTag());
            $this->assertSame(1, $marcSpec->getFieldIndexStart());
            $this->assertSame(3, $marcSpec->getFieldIndexEnd());
            
           $marcSpec->decode('245$a$b$c');
            $this->assertSame('245', $marcSpec->getFieldTag());
            $this->assertSame(['a'=>['tag'=>'a','start'=>0],'b'=>['tag'=>'b','start'=>0],'c'=>['tag'=>'c','start'=>0]], $marcSpec->getSubfields());
            
           
            $marcSpec->decode('245$![0]$"[1-3]$#[0-1]$$$%$&$\'$($)$*$+$-$.$/$:$;$<$=$>$?$[$]');
            $this->assertSame(
                [
                    "!"=>['tag'=>"!",'start'=>0,'end'=>0],
                    "\""=>['tag'=>"\"",'start'=>1,'end'=>3],
                    "#"=>['tag'=>"#",'start'=>0,'end'=>1],
                    "$"=>['tag'=>"$",'start'=>0],
                    "%"=>['tag'=>"%",'start'=>0],
                    "&"=>['tag'=>"&",'start'=>0],
                    "'"=>['tag'=>"'",'start'=>0],
                    "("=>['tag'=>"(",'start'=>0],
                    ")"=>['tag'=>")",'start'=>0],
                    "*"=>['tag'=>"*",'start'=>0],
                    "+"=>['tag'=>"+",'start'=>0],
                    "-"=>['tag'=>"-",'start'=>0],
                    "."=>['tag'=>".",'start'=>0],
                    "/"=>['tag'=>"/",'start'=>0],
                    ":"=>['tag'=>":",'start'=>0],
                    ";"=>['tag'=>";",'start'=>0],
                    "<"=>['tag'=>"<",'start'=>0],
                    "="=>['tag'=>"=",'start'=>0],
                    ">"=>['tag'=>">",'start'=>0],
                    "?"=>['tag'=>"?",'start'=>0],
                    "["=>['tag'=>"[",'start'=>0],
                    "]"=>['tag'=>"]",'start'=>0]
                ], $marcSpec->getSubfields());
            
            $marcSpec->decode('245$a$b_1a');
            $this->assertSame('1', $marcSpec->getIndicator1());
            $this->assertSame('a', $marcSpec->getIndicator2());

    }
    

    
    /**
     * test character position and range
     */
    public function testValidMarcSpec2()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->decode('LDR/0-3');
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            $this->assertSame(0, $marcSpec->getCharStart());
            $this->assertSame(3, $marcSpec->getCharEnd());
            $this->assertSame(4, $marcSpec->getCharLength());
    }
    
    /**
     * test index and subfield range
     */
    public function testValidMarcSpec21()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->decode('245$-[1-2]');
    }

        
     /**
     * test character range
     */
    public function testValidMarcSpec22()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->decode('245/#');
            $this->assertSame(1, $marcSpec->getCharLength());
            $marcSpec->decode('245/#-#');
            $this->assertSame(1, $marcSpec->getCharLength());
            $marcSpec->decode('245/#-0');
            $this->assertSame(1, $marcSpec->getCharLength());
            $marcSpec->decode('245/#-1');
            $this->assertSame(2, $marcSpec->getCharLength());
            $marcSpec->decode('245/0-#');
            $this->assertSame(0, $marcSpec->getCharStart());
            $this->assertSame("#", $marcSpec->getCharEnd());
            $this->assertSame(null, $marcSpec->getCharLength());
    }

    

    /**
     * test character position and range
     */
    public function testSetAndGet()
    {
            $marcSpec = $this->marcspec();
            $marcSpec->setFieldTag('LDR');
            $marcSpec->setCharStartEnd(0,3);
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            $this->assertSame(0, $marcSpec->getCharStart());
            $this->assertSame(3, $marcSpec->getCharEnd());
            $this->assertSame(4, $marcSpec->getCharLength());
            
            $marcSpec = $this->marcspec();
            $marcSpec->setFieldTag('LDR');
            $marcSpec->setCharStartEnd("#",3);
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            $this->assertSame("#", $marcSpec->getCharStart());
            $this->assertSame(3, $marcSpec->getCharEnd());
            $this->assertSame(4, $marcSpec->getCharLength());
            
            $marcSpec = $this->marcspec();
            $marcSpec->setFieldTag('LDR');
            $marcSpec->setCharStartLength(0,4);
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            $this->assertSame(0, $marcSpec->getCharStart());
            $this->assertSame(3, $marcSpec->getCharEnd());
            $this->assertSame(4, $marcSpec->getCharLength());
                        
            $marcSpec = $this->marcspec();
            $marcSpec->setFieldTag('LDR');
            $marcSpec->setCharStartLength("#",4);
            $this->assertSame('LDR', $marcSpec->getFieldTag());
            $this->assertSame("#", $marcSpec->getCharStart());
            $this->assertSame(3, $marcSpec->getCharEnd());
            $this->assertSame(4, $marcSpec->getCharLength());
            
            $marcSpec = $this->marcspec();
            $marcSpec->setFieldTag('245');
            $marcSpec->addSubfields('$a-c');
            $marcSpec->addSubfields('$d[3]');
            $marcSpec->addSubfields('$e[4]');
            $marcSpec->addSubfields('$f[5-6]');
            $marcSpec->setIndicator1('x');
            $marcSpec->setIndicator2('0');
            $this->assertSame('245', $marcSpec->getFieldTag());
            $this->assertSame('x', $marcSpec->getIndicator1());
            $this->assertSame('0', $marcSpec->getIndicator2());
            $this->assertSame(
                [
                    'a'=>['tag'=>'a','start'=>0],
                    'b'=>['tag'=>'b','start'=>0],
                    'c'=>['tag'=>'c','start'=>0],
                    'd'=>['tag'=>'d','start'=>3,'end'=>3],
                    'e'=>['tag'=>'e','start'=>4,'end'=>4],
                    'f'=>['tag'=>'f','start'=>5,'end'=>6],
                ], $marcSpec->getSubfields());
            
    }
    
    /**
     * test encoding
     */
    public function testEncode()
    {
        $marcSpec = $this->marcspec();
        $marcSpec->decode('245');
        $this->assertSame('245', $marcSpec->encode());
        
        $marcSpec->decode('245$a');
        $this->assertSame('245$a', $marcSpec->encode());
        
        $marcSpec->decode('245_01$a');
        $this->assertSame('245$a_01', $marcSpec->encode());
        
        $marcSpec->decode('245_1');
        $this->assertSame('245_1', $marcSpec->encode());
        
        $marcSpec->decode('245__0');
        $this->assertSame('245__0', $marcSpec->encode());
        
        $marcSpec->decode('245_1_');
        $this->assertSame('245_1', $marcSpec->encode());
        
        $marcSpec->decode('007/1');
        $this->assertSame('007/1', $marcSpec->encode());
        $this->assertSame(1, $marcSpec->getCharLength());
        
        $marcSpec->decode('007/1-3');
        $this->assertSame('007/1-3', $marcSpec->encode());
        $this->assertSame(3, $marcSpec->getCharLength());
        
        $marcSpec->decode('300[1]');
        $this->assertSame('300[1]', $marcSpec->encode());
        
        $marcSpec->decode('300[1-3]');
        $this->assertSame('300[1-3]', $marcSpec->encode());        
        
        $marcSpec->decode('300$a[0]');
        $this->assertSame('300$a[0]', $marcSpec->encode());        
        
        $marcSpec->decode('300$a[1-3]');
        $this->assertSame('300$a[1-3]', $marcSpec->encode());
    }
}
