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

/**
 * MARCspec subspec interface
 */
interface SubspecInterface
{

    /**
    * Constructor for SubSpec
    *
    * @param null|MARCspecInterface|ComparisonString $leftSubTerm
    * @param null|string $operator
    * @param MARCspecInterface|ComparisonString $rightSubTerm
    */
    public function __construct($leftSubTerm, $operator, $rightSubTerm);

    /**
     * Get subspecs operator
     * 
     * @return string The operator
     */ 
    public function getOperator();
    
    /**
     * Get subspecs operator
     * 
     * @return null|MARCspecInterface|ComparisonString The left hand subterm
     */ 
    public function getLeftSubTerm();
    
    /**
     * Get subspecs operator
     * 
     * @return MARCspecInterface|ComparisonString The right hand subterm
     */ 
    public function getRightSubTerm();
    
    /**
    * encodes SubSpec as string
    *
    * @api
    * 
    * @return string
    */
    public function __toString();
    
    /**
     * Serialize SubSpec as JSON
     * 
     * @api
     * 
     * @return array
     */
    public function jsonSerialize();

} // EOI

