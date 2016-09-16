<?php
namespace HackUtils\regex {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils\vector;
  use \HackUtils\map;
  use function \HackUtils\new_null;
  const CASELESS = "i";
  const MULTILINE = "m";
  const DOTALL = "s";
  const EXTENDED = "x";
  const ANCHORED = "A";
  const DOLLAR_ENDONLY = "D";
  const UNGREEDY = "U";
  const EXTRA = "X";
  const UTF8 = "u";
  const STUDY = "S";
  function quote($text) {
    return \preg_quote($text);
  }
  function match($regex, $subject, $options = "", $offset = 0) {
    $match = array();
    $count = \preg_match(
      _compose($regex, $options),
      $subject,
      $match,
      \PREG_OFFSET_CAPTURE,
      $offset
    );
    _check_last_error();
    return \hacklib_cast_as_boolean($count) ? _fix_match($match) : new_null();
  }
  function match_all($regex, $subject, $options, $offset = 0) {
    $matches = array();
    \preg_match_all(
      _compose($regex, $options),
      $subject,
      $matches,
      \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE,
      $offset
    );
    return vector\map(
      $matches,
      function($match) {
        return _fix_match($match);
      }
    );
  }
  function replace(
    $regex,
    $subject,
    $replacement,
    $limit = null,
    $options = ""
  ) {
    $result = \preg_replace(
      _compose($regex, $options),
      $replacement,
      $subject,
      ($limit === null) ? (-1) : \max(0, $limit)
    );
    _check_last_error();
    if (!\hacklib_cast_as_boolean(\is_string($result))) {
      throw new Exception("preg_replace() failed");
    }
    return $result;
  }
  function split($regex, $subject, $limit = null, $options = "") {
    $pieces = \preg_split(
      _compose($regex, $options),
      $subject,
      ($limit === null) ? (-1) : max(1, $limit)
    );
    _check_last_error();
    if (!\hacklib_cast_as_boolean(\is_array($pieces))) {
      throw new Exception("preg_split() failed");
    }
    return $pieces;
  }
  final class Exception extends \Exception {}
  function _compose($regex, $options = "") {
    return "/"._EscapeCache::escape($regex)."/".$options;
  }
  final class _EscapeCache {
    private static $cache = array();
    public static function escape($regex) {
      $escaped = self::$cache[$regex] ?? new_null();
      if ($escaped === null) {
        if (count(self::$cache) >= 10000) {
          self::$cache = array();
        }
        $escaped = self::$cache[$regex] = _escape($regex);
      }
      return $escaped;
    }
  }
  function _escape($regex) {
    $result = "";
    $length = strlen($regex);
    $escape = false;
    for ($i = 0; $i < $length; $i++) {
      $char = $regex[$i];
      if (\hacklib_cast_as_boolean($escape)) {
        $escape = false;
      } else {
        if ($char === "/") {
          $result .= "\\";
        } else {
          if ($char === "\\") {
            $escape = true;
          }
        }
      }
      $result .= $char;
    }
    return $result;
  }
  function _fix_match($match) {
    foreach ($match as $k => $v) {
      if (\hacklib_equals($v[1], -1)) {
        unset($match[$k]);
      }
    }
    return $match;
  }
  function _get_error_message($error) {
    switch ($error) {
      case PREG_NO_ERROR:
        return "No errors";
      case PREG_INTERNAL_ERROR:
        return "Internal PCRE error";
      case PREG_BACKTRACK_LIMIT_ERROR:
        return "Backtrack limit (pcre.backtrack_limit) was exhausted";
      case PREG_RECURSION_LIMIT_ERROR:
        return "Recursion limit (pcre.recursion_limit) was exhausted";
      case PREG_BAD_UTF8_ERROR:
        return "Malformed UTF-8 data";
      case PREG_BAD_UTF8_OFFSET_ERROR:
        return
          "The offset didn't correspond to the beginning of a valid UTF-8 code point";
      case 6:
        return "JIT stack space limit exceeded";
      default:
        return "Unknown error";
    }
  }
  function _check_last_error() {
    $error = \preg_last_error();
    if ($error !== \PREG_NO_ERROR) {
      throw new Exception(_get_error_message($error), $error);
    }
  }
}
