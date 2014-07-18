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
interface SubfieldInterface {

    /**
     * constructor for MARCspec field
     *
     * @api
     *
     * @access public
     * 
     * @param string $subfield The subfield spec
     */
     public function __construct($subfield);
    
    /**
    *
    * Get the field tag
    *
    * @api
    * 
    * @access public
    * 
    * @return string $fieldTag The field tag
    */
    public function getTag();

    /**
        *
     * Set the field index starting and ending position
        *
     * @api
     * 
     * @access public
     * 
     * @param int|string $start The index starting position
     * @param int|string|null $end The index ending position
     */
    public function setIndexStartEnd($start,$end = null);
    
    /**
        *
     * Set the field index starting and ending position via length
        *
     * @api
     * 
     * @access public
        *
     * @param int|string $start The index starting position
     * @param int $length The length count
     */
    public function setIndexStartLength($start,$length);
    
    /**
        *
     * Get the character starting position
        *
     * @api
     * 
     * @access public
     * 
     * @return null|int|string $indexStart The field index starting position
     */
    public function getIndexStart();
    
    /**
        *
     * Get the field index ending position
        *
     * @api
     * 
     * @access public
     * 
     * @return null|int $indexEnd The field index ending position
     */
    public function getIndexEnd();
    
    /**
        *
     * Set character starting and ending position
        *
     * @api
     * 
     * @access public
     * 
     * @param int|string $start The character starting position
     * @param int|string|null $end The character ending position
        *
     */
    public function setCharStartEnd($start,$end = null);
    
    /**
        *
     * Set character starting and ending position via start and length
     * 
     * @api
     * 
     * @access public
     * 
     * @param int|string $start The character starting position
     * @param int $length The character length count
        *
     */
    public function setCharStartLength($start,$length);
    
    /**
        *
     * Get the character starting position
        *
     * @api
     * 
     * @access public
     * 
     * @return null|int $charStart The character starting position 
     */
    public function getCharStart();
    
    
    /**
        *
     * Get the character ending position
        *
     * @api
     * 
     * @access public
     * 
     * @return null|int $charEnd The character ending position 
     */
    public function getCharEnd();
    
    /**
    *
    * Get length of character range
    *
    * @api
    * 
    * @access public
    * 
    * @return null|int $length The character length
    * 
    * @throws \InvalidArgumentException if length is less than 1
    */
    public function getCharLength();
    
    /**
    * get array of subspecs
    *
    * @api
    * 
    * @return null|array
    */
    public function getSubSpecs();
    
    /**
    * add a subspec to the array of subspecs
    *
    * @api
    * 
    * @param SubSpecInterface|array[SubSpecInterface]
    * 
    * @return null|array
    */
    public function addSubSpec($SubSpec);
    
    /**
     * encodes Field as string
     *
     * @api
     * 
     * @return string
     */
    public function __toString();
    
    /**
     * Serialize Field as JSON
     * 
     * @api
     * 
     * @return array
     */
    public function jsonSerialize();

} // EOI

