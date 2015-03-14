<?php
/**
 * Request-Route-Ракушки-Response
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Little
 */
namespace Ranyuen\Little;

require_once 'vendor/hafriedlander/php-peg/autoloader.php';

use hafriedlander\Peg\Parser;
use Ranyuen\Di\Dispatcher\Dispatcher;

/**
 * Path DSL compiler.
 */
class PathCompiler extends Parser\Basic
{
/* Main: Element * */
protected $match_Main_typestack = array('Main');
function match_Main ($stack = array()) {
	$matchrule = "Main"; $result = $this->construct($matchrule, $matchrule, null);
	while (true) {
		$res_0 = $result;
		$pos_0 = $this->pos;
		$matcher = 'match_'.'Element'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else {
			$result = $res_0;
			$this->pos = $pos_0;
			unset( $res_0 );
			unset( $pos_0 );
			break;
		}
	}
	return $this->finalise($result);
}

public function Main_Element (&$result, $sub) {
        if (!isset($result['val'])) {
            $result['val'] = [];
        }
        $result['val'][] = $sub;
    }

/* Element: Param | Splat | Text */
protected $match_Element_typestack = array('Element');
function match_Element ($stack = array()) {
	$matchrule = "Element"; $result = $this->construct($matchrule, $matchrule, null);
	$_8 = NULL;
	do {
		$res_1 = $result;
		$pos_1 = $this->pos;
		$matcher = 'match_'.'Param'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) {
			$this->store( $result, $subres );
			$_8 = TRUE; break;
		}
		$result = $res_1;
		$this->pos = $pos_1;
		$_6 = NULL;
		do {
			$res_3 = $result;
			$pos_3 = $this->pos;
			$matcher = 'match_'.'Splat'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_6 = TRUE; break;
			}
			$result = $res_3;
			$this->pos = $pos_3;
			$matcher = 'match_'.'Text'; $key = $matcher; $pos = $this->pos;
			$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
			if ($subres !== FALSE) {
				$this->store( $result, $subres );
				$_6 = TRUE; break;
			}
			$result = $res_3;
			$this->pos = $pos_3;
			$_6 = FALSE; break;
		}
		while(0);
		if( $_6 === TRUE ) { $_8 = TRUE; break; }
		$result = $res_1;
		$this->pos = $pos_1;
		$_8 = FALSE; break;
	}
	while(0);
	if( $_8 === TRUE ) { return $this->finalise($result); }
	if( $_8 === FALSE) { return FALSE; }
}

public function Element_Param (&$result, $sub) {
        $result['type']        = 'Param';
        $result['val']         = $sub['val'];
        $result['is_optional'] = $sub['is_optional'];
        $result['is_wildcard'] = $sub['is_wildcard'];
    }

public function Element_Splat (&$result, $sub) {
        $result['type'] = 'Splat';
    }

public function Element_Text (&$result, $sub) {
        $result['type']        = 'Text';
        $result['val']         = $sub['text'];
        $result['is_optional'] = $sub['is_optional'];
    }

/* Param: ':' > ParamName > Splat ? > Option ? */
protected $match_Param_typestack = array('Param');
function match_Param ($stack = array()) {
	$matchrule = "Param"; $result = $this->construct($matchrule, $matchrule, null);
	$_17 = NULL;
	do {
		if (substr($this->string,$this->pos,1) == ':') {
			$this->pos += 1;
			$result["text"] .= ':';
		}
		else { $_17 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$matcher = 'match_'.'ParamName'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_17 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$res_14 = $result;
		$pos_14 = $this->pos;
		$matcher = 'match_'.'Splat'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else {
			$result = $res_14;
			$this->pos = $pos_14;
			unset( $res_14 );
			unset( $pos_14 );
		}
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$res_16 = $result;
		$pos_16 = $this->pos;
		$matcher = 'match_'.'Option'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else {
			$result = $res_16;
			$this->pos = $pos_16;
			unset( $res_16 );
			unset( $pos_16 );
		}
		$_17 = TRUE; break;
	}
	while(0);
	if( $_17 === TRUE ) { return $this->finalise($result); }
	if( $_17 === FALSE) { return FALSE; }
}

public function Param_ParamName (&$result, $sub) {
        $result['val']         = $sub['text'];
        $result['is_wildcard'] = false;
        $result['is_optional'] = false;
    }

public function Param_Splat (&$result, $sub) {
        $result['is_wildcard'] = true;
    }

public function Param_Option (&$result, $sub) {
        $result['is_optional'] = true;
    }

/* ParamName: /\w+/ */
protected $match_ParamName_typestack = array('ParamName');
function match_ParamName ($stack = array()) {
	$matchrule = "ParamName"; $result = $this->construct($matchrule, $matchrule, null);
	if (( $subres = $this->rx( '/\w+/' ) ) !== FALSE) {
		$result["text"] .= $subres;
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* Splat: '*' */
protected $match_Splat_typestack = array('Splat');
function match_Splat ($stack = array()) {
	$matchrule = "Splat"; $result = $this->construct($matchrule, $matchrule, null);
	if (substr($this->string,$this->pos,1) == '*') {
		$this->pos += 1;
		$result["text"] .= '*';
		return $this->finalise($result);
	}
	else { return FALSE; }
}


/* Text: TextContent > Option ? */
protected $match_Text_typestack = array('Text');
function match_Text ($stack = array()) {
	$matchrule = "Text"; $result = $this->construct($matchrule, $matchrule, null);
	$_24 = NULL;
	do {
		$matcher = 'match_'.'TextContent'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else { $_24 = FALSE; break; }
		if (( $subres = $this->whitespace(  ) ) !== FALSE) { $result["text"] .= $subres; }
		$res_23 = $result;
		$pos_23 = $this->pos;
		$matcher = 'match_'.'Option'; $key = $matcher; $pos = $this->pos;
		$subres = ( $this->packhas( $key, $pos ) ? $this->packread( $key, $pos ) : $this->packwrite( $key, $pos, $this->$matcher(array_merge($stack, array($result))) ) );
		if ($subres !== FALSE) { $this->store( $result, $subres ); }
		else {
			$result = $res_23;
			$this->pos = $pos_23;
			unset( $res_23 );
			unset( $pos_23 );
		}
		$_24 = TRUE; break;
	}
	while(0);
	if( $_24 === TRUE ) { return $this->finalise($result); }
	if( $_24 === FALSE) { return FALSE; }
}

public function Text_TextContent (&$result, $sub) {
        $result['val']       = $sub['text'];
        $result['is_optional'] = false;
    }

public function Text_Option (&$result, $sub) {
        $result['is_optional'] = true;
    }

/* TextContent: /[^:*?\w]/ | /\w+/ */
protected $match_TextContent_typestack = array('TextContent');
function match_TextContent ($stack = array()) {
	$matchrule = "TextContent"; $result = $this->construct($matchrule, $matchrule, null);
	$_29 = NULL;
	do {
		$res_26 = $result;
		$pos_26 = $this->pos;
		if (( $subres = $this->rx( '/[^:*?\w]/' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_29 = TRUE; break;
		}
		$result = $res_26;
		$this->pos = $pos_26;
		if (( $subres = $this->rx( '/\w+/' ) ) !== FALSE) {
			$result["text"] .= $subres;
			$_29 = TRUE; break;
		}
		$result = $res_26;
		$this->pos = $pos_26;
		$_29 = FALSE; break;
	}
	while(0);
	if( $_29 === TRUE ) { return $this->finalise($result); }
	if( $_29 === FALSE) { return FALSE; }
}


/* Option: '?' */
protected $match_Option_typestack = array('Option');
function match_Option ($stack = array()) {
	$matchrule = "Option"; $result = $this->construct($matchrule, $matchrule, null);
	if (substr($this->string,$this->pos,1) == '?') {
		$this->pos += 1;
		$result["text"] .= '?';
		return $this->finalise($result);
	}
	else { return FALSE; }
}




    private $conditions = [];

    public function __construct($path, $conditions = [])
    {
        parent::__construct($path);
        $this->conditions = $conditions;
    }

    /**
     * Compile the route path DSL to a regex.
     *
     * @param string $path Path DSL.
     *
     * @return string
     */
    public function compile()
    {
        $regex = '';
        foreach ($this->match_Main()['val'] as $element) {
            switch ($element['type']) {
                case 'Param':
                    $regex .= $this->compileParam($element);
                    break;
                case 'Splat':
                    $regex .= '(.*?)';
                    break;
                case 'Text':
                    $regex .= preg_quote($element['val'], '#');
                    if ($element['is_optional']) {
                        $regex .= '?';
                    }
                    break;
            }
        }
        return "#\A$regex\z#";
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function compileParam($element)
    {
        $regex = '';
        if (isset($this->conditions[$element['val']]) && is_string($cond = $this->conditions[$element['val']])) {
            if (Dispatcher::isRegex($cond)) {
                preg_match($cond, '#\A.(.*).[imsxeADSUXJu]*\z#', $matches);
                $cond = $matches[1];
            } else {
                $cond = preg_quote($cond, '#');
            }
            $regex .= '(?<'.$element['val'].'>'.$cond.')';
        } elseif ($element['is_wildcard']) {
            $regex .= '(?<'.$element['val'].'>.+?)';
        } else {
            $regex .= '(?<'.$element['val'].'>\w+?)';
        }
        if ($element['is_optional']) {
            $regex .= '?';
        }
        return $regex;
    }
}
// vim:ft=php:
