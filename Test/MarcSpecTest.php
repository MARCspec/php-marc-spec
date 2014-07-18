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
      * assert same subfields
     */
     public function testValidMarcSpec1()
     {
          
         $marcSpec = $this->marcspec('245$a-c');
         $_subfields = $marcSpec->getSubfields();
         $this->assertSame(3, count($_subfields));
     }
     /**
      * assert same subfields
     */
     public function testValidMarcSpec2()
     {
          
         $marcSpec = $this->marcspec('245');
         $marcSpec->addSubfields('$d-f');
         $_subfields = $marcSpec->getSubfields();
         $this->assertSame(3, count($_subfields));
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
     * assert same subspecs
     */
    public function testValidMarcSpec3()
    {
        $field = new Field('245');
        $marcSpec = MARCspec::setField($field);
        $marcSpec->addSubfields('$d{$c/#=\.}{?$a}');
        $_subfields = $marcSpec->getSubfields();
        $this->assertSame(1, count($_subfields));
        $_sf = $marcSpec->getSubfield('d');
        $sub = $_sf[0]->getSubSpecs();
        $left = $sub[0]->getLeftSubTerm();
        $leftField = $left->getField();
        $this->assertSame('245', $leftField->getTag());
    }
    


}
