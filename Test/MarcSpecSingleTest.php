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


class MarcSpecSingleTest extends \PHPUnit_Framework_TestCase
{
   


    /**
     * assert same subspecs
     */
    public function testValidMarcSpec3()
    {
        $MS = new MARCspec('245$a{$b}');
        print_r($MS);
    }
    


}
