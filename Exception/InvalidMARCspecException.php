<?php
/**
* MARCspec is the specification of a reference, encoded as string, to a set of data from within a MARC record.
* 
* @author Carsten Klee <mailme.klee@yahoo.de>
* @package CK\MARCspec
* @copyright For the full copyright and license information, please view the LICENSE 
* file that was distributed with this source code.
*/
namespace CK\MARCspec\Exception;

/**
 * Exception is thrown if an invalid MARCspec is detected
 */
class InvalidMARCspecException extends \UnexpectedValueException {


    const METHOD       = 'In method ';
    const ARGUMENT     = 'Tried to parse ';
    const UNKNOWN      = 'Unknown Error.';
    const MS           = 'MARCspec. ';
    const FS           = 'Fieldspec. ';
    const SF           = 'Subfieldspec. ';
    const PR           = 'PositionOrRange. ';
    const CS           = 'ComparisonString. ';
    const SS           = 'Subspec. ';
    const RANGE        = 'Only ranges between "a-z", "A-Z" or "0-9" allowed.';
    const BRACKET      = 'Unequal count of opening and closing brackets';
    const SPACE        = 'Whitespace detected.';
    const MISSINGFIELD = 'Cannot detect fieldspec.';
    const MISSINGRIGHT = 'Right hand subTerm is missing.';
    const MINIMUM2     = 'Spec must be at least two characters long.';
    const MINIMUM3     = 'Spec must be at least three characters long.';
    const MINIMUM4     = 'Spec must be at least four characters long.';
    const LENGTH       = 'Invalid spec length.';
    const LENGTH3      = 'Invalid spec length. At minimum spec must be three characters long.';
    const PREFIX       = 'Missing prefixed character "$".';
    const ESCAPE       = 'Unescaped character detected';
    const DETECTEDSS   = 'Detected Subspec. Use method addSubSpec to add subspecs.';
    const INDEX        = 'Invalid index detected.';
    const PRCHAR       = 'For character position or range minimum one digit or character # is required.';
    const USELESS      = 'Detected useless data fragment.';
    const FTAG         = 'For fieldtag only "." and digits and lowercase alphabetic or digits and upper case alphabetics characters are allowed';
    const LENGTHIND    = 'For indicators only two characters at are allowed.';
    const INDCHAR1     = 'At minimum one indicator must be a digit or a lowercase alphabetic character.';
    const INDCHAR2     = 'For indicators only digits, lowercase alphabetic characters and "_" are allowed.';
    const NEGATIVE     = 'Ending character or index position must be equal or higher than starting character or index position.';
    const PR1          = 'Assuming index or character position or range. Minimum one character is required. None given.';
    const PR2          = 'Assuming index or character position or range. Only digits, the character # and one "-" is allowed.';
    const PR3          = 'Assuming index or character range. At least two digits or the character # must be present.';
    const PR4          = 'Assuming index or character position or range. First character must not be "-".';
    const PR5          = 'Assuming index or character position or range. Only one "-" character allowed.';
    const PR6          = 'Assuming index or character position or range. Only digits and one "-" is allowed.';
    const PR7          = 'Assuming index or character position or range. Starting index must be positive int, 0 or "#".';
    const PR8          = 'Assuming index or character position or range. Ending index must be a higher number (or equal) than starting index.';
    const MISSINGTAG   = 'Unexpected empty subfield tag';
    const SFCHAR       = 'For subfields only digits, lowercase alphabetic characters or one of "!"#$%&\'()*+,-./0-9:;<=>?[\]^_`a-z{}~" are allowed.';
    const SFRANGE      = 'Assuming subfield range. Use CK\MARCspec::addSubfields() to add multiple subfields via a subfield range.';
    const MISSINGSLASH = 'Assuming subfield character position or range. Missing "/" delimiter';
    const OPERATOR     = 'Operator must be one of "=" / "!=" / "~" / "!~" / "!" / "?".';
    const HINTESCAPED  = 'Hint: Check for unescaped characters.';
    const CHARORIND    = 'Either characterSpec or indicators are allowed.';
    const CHARANDSF    = 'Either characterSpec for field or subfields are allowed.';
    
    public function __construct($message, $context = null)
    {
        $context = (!is_null($context)) ? "\n".self::ARGUMENT.'"'.$context.'"' : null;
        
        parent::__construct("Detected invalid ".$message.$context);
    }
}
