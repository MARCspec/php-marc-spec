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
 * MARCspec comparison string interface
 */
interface ComparisonStringInterface
{

    /**
    * Constructor for ComparisonString
    *
    * @api
    *
    * @param string $raw The escaped comparison string
    */
    public function __construct($raw);
    
    /**
    * Get unescaped comparable string
    *
    * @api
    *
    * @return string The comparable string
    */
    public function getComparable();
    
    /**
    * Get raw escaped string
    *
    * @api
    *
    * @return string The escaped string
    */
    public function getRaw();
    
    /**
    * Escape a comparison string
    *
    * @api
    *
    * @param string $arg The unescaped string
    *
    * @return string The escaped string
    */
    public static function escape($arg);
    
    /**
    * encodes ComparisonString as string
    *
    * @api
    *
    * @return string
    */
    public function __toString();
    
    /**
     * Serialize ComparisonString as JSON
     *
     * @api
     *
     * @return array
     */
    public function jsonSerialize();
} // EOI
