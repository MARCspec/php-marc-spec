<?php
/*
* (c) Carsten Klee <mailme.klee@yahoo.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Test;

use CK\MARCspec\MARCspec;
use CK\MARCspec\SubSpec;
use CK\MARCspec\Field;
use CK\MARCspec\Exception\InvalidMARCspecException;

class SubSpecTest extends \PHPUnit_Framework_TestCase
{
    
    public function subspec($arg1,$arg2,$arg3)
    {
        return new SubSpec($arg1,$arg2,$arg3);
    }    
    public function marcspec($arg)
    {
        return new MARCspec($arg);
    }
    
    /****
    * invalid data types
    ***/
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidArgument1Decode()
    {
        $fieldSpec = $this->subspec('245','=','300');
    }
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidArgument2Decode()
    {
        $fieldSpec = $this->subspec(new Field('245'),'=',new Field('245'));
    }
    
    /**
     * @expectedException CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function testInvalidArgument3Decode()
    {
        $this->marcspec('...{$a{$b}}');
    }

    



    /**
     * assert true
     */
    public function testValidFieldSpec1()
    {
        $marcspec1 = $this->marcspec('245$a');
        $marcspec2 = $this->marcspec('245$b');
        $subspec = $this->subspec($marcspec1,'=',$marcspec2);
        $left = $subspec->getLeftSubTerm();
        $right = $subspec->getRightSubTerm();
        $operator = $subspec->getOperator();
        $field =  $left->getField();
        $this->assertSame('245', $field->getTag());
        $subfields = $right->getSubfields();
        $this->assertInstanceOf('CK\MARCspec\Subfield',$subfields[0] );
        $this->assertSame('=', $operator);
    }
    
    /**
     * assert true
     */
    public function testValidFieldSpec2()
    {
        $marcspec1 = $this->marcspec('245$a');
        $marcspec2 = $this->marcspec('245$b');
        $subspec = $this->subspec($marcspec1,null,$marcspec2);
        $this->assertNull($subspec->getLeftSubTerm());
        $right = $subspec->getRightSubTerm();
        $operator = $subspec->getOperator();
        $subfields = $right->getSubfields();
        $this->assertInstanceOf('CK\MARCspec\Subfield',$subfields[0] );
        $this->assertSame('?', $operator);
    }
    


}
