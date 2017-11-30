<?php
/**
 * MARCspec is the specification of a reference, encoded as string, to a set of data
 * from within a MARC record.
 *
 * @author Carsten Klee <mailme.klee@yahoo.de>
 * @copyright For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CK\MARCspec;

use CK\MARCspec\Exception\InvalidMARCspecException;

/**
 * Parses a MARCspec into an array.
 */
class MARCspecParser
{
    /**
     * @var string Regex for field tag
     */
    protected $FIELDTAG;
    /**
     * @var string Regex for position or range
     */
    protected $POSITIONORRANGE;
    /**
     * @var string Regex for index
     */
    protected $INDEX;
    /**
     * @var string Regex for charpos
     */
    protected $CHARPOS;
    /**
     * @var string Regex for indicator position
     */
    protected $INDICATOR;
    /**
     * @var string Regex for field subspecs
     */
    protected $F_SUBSPECS;
    /**
     * @var string Regex for subfield subspecs
     */
    protected $SF_SUBSPECS;
    /**
     * @var string Regex for subfields
     */
    protected $SUBFIELDS;
    /**
     * @var string Regex for field
     */
    protected $FIELD;
    /**
     * @var string Regex for the MARCspec
     */
    protected $MARCSPEC;
    /**
     * @var string Regex for subfield range
     */
    protected $SUBFIELDTAGRANGE;
    /**
     * @var string Regex for subfield tag
     */
    protected $SUBFIELDTAG;
    /**
     * @var string Regex for subfield
     */
    protected $SUBFIELD;
    /**
     * @var string Regex for leftSubTerm
     */
    protected $LEFTSUBTERM;
    /**
     * @var string Regex for operator
     */
    protected $OPERATOR;
    /**
     * @var string Regex for subterms
     */
    protected $SUBTERMS;
    /**
     * @var string Regex for subspec
     */
    protected $SUBSPEC;

    /**
     * @var array The parsed MARCspec
     */
    public $parsed = [];

    /**
     * @var array The parsed subfieldspecs
     */
    public $subfields = [];

    /**
     * @var string The parsed indicator position
     */
    public $indicatorpos;

    public function __construct($spec = null)
    {
        $this->setConstants();

        if (is_null($spec)) {
            return;
        }

        $this->parse($spec);

        if (array_key_exists('subfields', $this->parsed)) {
            $this->subfields = $this->parseSubfields($this->parsed['subfields']);
        }
    }

    /**
     * parses MARCspec.
     *
     * @param string $marcspec The MARCspec
     *
     * @throws CK\MARCspec\Exception\InvalidMARCspecException
     */
    public function parse($marcspec)
    {
        if (!preg_match_all('/'.$this->MARCSPEC.'/', $marcspec, $this->parsed, PREG_SET_ORDER)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::MISSINGFIELD,
                $marcspec
            );
        }

        $this->parsed = array_filter($this->parsed[0], 'strlen');

        if (!array_key_exists('field', $this->parsed)) { // TODO: check if 'tag' is the required key
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::FTAG,
                $marcspec
            );
        }

        if (strlen($this->parsed[0]) !== strlen($marcspec)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::FS.
                InvalidMARCspecException::USELESS,
                $marcspec
            );
        }

        if (array_key_exists('charpos', $this->parsed)) {
            if (array_key_exists('indicatorpos', $this->parsed)) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::FS.
                    InvalidMARCspecException::CHARORIND,
                    $marcspec
                );
            }

            if (array_key_exists('subfields', $this->parsed)) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::FS.
                    InvalidMARCspecException::CHARORSF,
                    $marcspec
                );
            }
        }

        if (array_key_exists('subfields', $this->parsed)) {
            if (array_key_exists('indicatorpos', $this->parsed)) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::FS.
                    InvalidMARCspecException::INDORSF,
                    $marcspec
                );
            }
        }

        if (array_key_exists('subspecs', $this->parsed)) {
            $_subSpecs = $this->matchSubSpecs($this->parsed['subspecs']);

            $this->parsed['subspecs'] = [];

            foreach ($_subSpecs as $subSpec) {
                if (1 < count($subSpec)) {
                    foreach ($subSpec as $orSubSpec) {
                        $_or[] = $this->matchSubTerms($orSubSpec);
                    }
                    $this->parsed['subspecs'][] = $_or; // TODO: Check if array is required since $_or is an array
                } else {
                    $this->parsed['subspecs'][] = $this->matchSubTerms($subSpec[0]);
                }
            }
        }
    }

    /**
     * Matches subfieldspecs.
     *
     * @param string $subfieldspec A string of one or more subfieldspecs
     */
    public function parseSubfields($subfieldspec)
    {
        if (!preg_match_all('/'.$this->SUBFIELD.'/', $subfieldspec, $_subfieldMatches, PREG_SET_ORDER)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::SFCHAR,
                $subfieldspec
            );
        }
        /*
        * For each subfield (array) do anonymous function
        * - first filter empty elements
        * - second look for subspecs
        * - match subspecs and match subTerms
        * - return everything in the array of subfields
        */
        array_walk(
            $_subfieldMatches,
            function (&$_subfield) use (&$test) {
                $_subfield = array_filter($_subfield, 'strlen');

                $test .= $_subfield['subfield'];

                if (array_key_exists('subspecs', $_subfield)) {
                    $_ss = [];

                    if (!$_subfieldSubSpecs = $this->matchSubSpecs($_subfield['subspecs'])) {
                        // TODO: raise error;
                    }

                    foreach ($_subfieldSubSpecs as $key => $_subfieldSubSpec) {
                        if (1 < count($_subfieldSubSpec)) {
                            foreach ($_subfieldSubSpec as $orSubSpec) {
                                $_or[] = $this->matchSubTerms($orSubSpec);
                            }
                            $_ss[] = $_or;
                        } else {
                            $_ss[] = $this->matchSubTerms($_subfieldSubSpec[0]);
                        }
                    }

                    $_subfield['subspecs'] = $_ss;
                }
            }
        );

        if ($test !== $subfieldspec) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::USELESS,
                $subfieldspec
            );
        }

        return $_subfieldMatches;
    }

    /**
     * calls parseSubfields but makes sure only one subfield is present.
     *
     * @param string $subfieldspec A subfieldspec
     *
     * @return array An Array of subfieldspec
     */
    public function subfieldToArray($subfieldspec)
    {
        if (!$_sf = $this->parseSubfields($subfieldspec)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::UNKNOWN,
                $subfieldspec
            );
        }

        if (1 < count($_sf)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::MULTISF,
                $subfieldspec
            );
        }

        if ($_sf[0]['subfield'] !== $subfieldspec) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SF.
                InvalidMARCspecException::USELESS,
                $subfieldspec
            );
        }

        return $_sf[0];
    }

    /**
     * parses subspecs into an array.
     *
     * @param string $subSpecs One or more subspecs
     *
     * @return array Array of subspecs
     */
    private function matchSubSpecs($subSpecs)
    {
        $_subSpecs = [];
        if (!preg_match_all('/'.$this->SUBSPEC.'/U', $subSpecs, $_subSpecMatches, PREG_SET_ORDER)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SS.
                InvalidMARCspecException::UNKNOWN,
                $subSpecs
            );
        }

        foreach ($_subSpecMatches as $key => $_subSpecMatch) {
            if (array_key_exists(1, $_subSpecMatch) && !empty($_subSpecMatch[1])) {
                $_subSpecs[$key] = preg_split('/(?<!\\\)\|/', $_subSpecMatch[1], -1, PREG_SPLIT_NO_EMPTY);
            } else {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SS.
                    InvalidMARCspecException::UNKNOWN,
                    $subSpecs
                );
            }
        }

        return $_subSpecs;
    }

    /**
     * Parses a single SubSpec into sunTerms.
     *
     * @param string $subSpec A single SubSpec
     *
     * @return array subTerms as array
     */
    private function matchSubTerms($subSpec)
    {
        if (preg_match('/(?<![\\\\\$])[\{\}]/', $subSpec, $_error, PREG_OFFSET_CAPTURE)) {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SS.
                InvalidMARCspecException::ESCAPE,
                $subSpec
            );
        }

        if (preg_match_all('/'.$this->SUBTERMS.'/', $subSpec, $_subTermMatches, PREG_SET_ORDER)) {
            if (empty($_subTermMatches[0]['operator'])) {
                $_subTermMatches[0]['operator'] = '?';
            }
            if (!$_subTermMatches[0]['rightsubterm']) {
                throw new InvalidMARCspecException(
                    InvalidMARCspecException::SS.
                    InvalidMARCspecException::MISSINGRIGHT,
                    $subSpec
                );
            }

            return array_filter($_subTermMatches[0], 'strlen');
        } else {
            throw new InvalidMARCspecException(
                InvalidMARCspecException::SS.
                InvalidMARCspecException::UNKNOWN,
                $subSpec
            );
        }
    }

    /**
     * Set regex variables (constant).
     */
    private function setConstants()
    {
        $this->FIELDTAG = '(?<tag>(?:[a-z0-9\.]{3,3}|[A-Z0-9\.]{3,3}|[0-9\.]{3,3}))';
        $this->POSITIONORRANGE = '(?:(?:(?:[0-9]+|#)\-(?:[0-9]+|#))|(?:[0-9]+|#))';
        $this->INDEX = '(?:\[(?<index>'.$this->POSITIONORRANGE.')\])?';
        $this->CHARPOS = '(?:\/(?<charpos>'.$this->POSITIONORRANGE.'))?';
        $this->INDICATORPOS = '(?:\^(?<indicatorpos>[12]))?';
        //$this->INDICATOR        = '(?:\^)(?<indicator>'.$this->INDICATORPOS.')?';
        $this->SUBSPECS = '(?<subspecs>(?:\{.+?(?<!(?<!(\$|\\\))(\$|\\\))\})+)?';
        $this->SUBFIELDS = '(?<subfields>\$.+)';
        $this->FIELD = '(?<field>(?:'.$this->FIELDTAG.$this->INDEX.'))';
        $this->MARCSPEC = '^'.$this->FIELD.'(?:'.$this->SUBFIELDS.'|(?:'.$this->INDICATORPOS.'|'.$this->CHARPOS.')'.$this->SUBSPECS.')$';
        $this->SUBFIELDTAGRANGE = '(?<subfieldtagrange>(?:[0-9a-z]\-[0-9a-z]))';
        $this->SUBFIELDTAG = '(?<subfieldtag>[\!-\?\[-\{\}-~])';
        $this->SUBFIELD = '(?<subfield>\$(?:'.$this->SUBFIELDTAGRANGE.'|'.$this->SUBFIELDTAG.')'.$this->INDEX.$this->CHARPOS.$this->SUBSPECS.')';
        $this->LEFTSUBTERM = '^(?<leftsubterm>(?:\\\(?:(?<=\\\)[\!\=\~\?]|[^\!\=\~\?])+)|(?:(?<=\$)[\!\=\~\?]|[^\!\=\~\?])+)?';
        $this->OPERATOR = '(?<operator>\!\=|\!\~|\=|\~|\!|\?)';
        $this->SUBTERMS = '(?:'.$this->LEFTSUBTERM.$this->OPERATOR.')?(?<rightsubterm>.+)$';
        $this->SUBSPEC = '(?:\{(.+)\})';
    }
}

$test = new \CK\MARCspec\MARCspecParser();
