<?php
/**
 * MARCspec is the specification of a reference, encoded as string, to a set of data from within a MARC record.
 * 
 * @author Carsten Klee <mailme.klee@yahoo.de>
 * @package CK\MARCspec
 * @copyright For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

namespace CK\MARCspec;

/**
 * MARCspec subfield interface
 */
interface MARCspecInterface {
    /**
     * Constructor for a MARCspec
     *
     * For a minimal MARCspec a field tag must be provided by default
     * 
     * @api
     * 
     * @param string|FieldInterface $spec The MARCspec as string or an instance of FieldInterface
     */
     public function __construct($spec);
    
    /**
     * Set the field
     *
     * @param FieldInterface $field Instance of FieldInterface
     * 
     * @return MARCspec Instance of MARCspec
     */
    public static function setField(FieldInterface $field);
    
    /**
     * Get the field
     *
     * @return FieldInterface An instance of FieldInterface
     */ 
    public function getField();
    
    /**
     * Add subfields
     * 
     * @param string|SubfieldInterface $subfields The subfield spec or instance of SubfieldInterface
     */
    public function addSubfields($subfields);
    
    /**
    * Get array of subfields
    *
    * @return null|array[SubfieldInterface] $subfields The array of instances of SubfieldInterface
    */
     public function getSubfields();
     
    /**
    * Get array of subfields with a specific tag
    *
    * @param string $subfieldTag The subfield tag
    * 
    * @return null|array $subfields [SubfieldInterface] $subfields The array of instances of SubfieldInterface with a specific tag
    */
     public function getSubfield($subfieldTag);
    
    /**
     * Encodes MARCspec as string
     *
     * @api
     * 
     * @return string The MARCspec as string
     */
    public function __toString();
    
    /**
     * Serialize MARCspec as JSON
     * 
     * @api
     * 
     * @return string The MARCspec as JSON encoded
     */
    public function jsonSerialize();

} // EOI

