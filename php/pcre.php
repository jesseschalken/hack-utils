<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  const PCRE_CASELESS = "i";
  const PCRE_MULTILINE = "m";
  const PCRE_DOTALL = "s";
  const PCRE_EXTENDED = "x";
  const PCRE_ANCHORED = "A";
  const PCRE_DOLLAR_ENDONLY = "D";
  const PCRE_UNGREEDY = "U";
  const PCRE_EXTRA = "X";
  const PCRE_UTF8 = "u";
  const PCRE_STUDY = "S";
  function pcre_match_get($match, $subPattern = 0) {
    $subPattern = get_or_null($match, $subPattern);
    return ($subPattern !== null) ? $subPattern[0] : new_null();
  }
  function pcre_match_offset($match, $subPattern = 0) {
    $subPattern = get_or_null($match, $subPattern);
    return ($subPattern !== null) ? $subPattern[1] : new_null();
  }
  function pcre_quote($text) {
    return \preg_quote($text);
  }
  function pcre_match($regex, $subject, $options = "", $offset = 0) {
    $match = array();
    $count = \preg_match(
      _pcre_compose($regex, $options),
      $subject,
      $match,
      \PREG_OFFSET_CAPTURE,
      $offset
    );
    _pcre_check_last_error();
    return $count ? _pcre_fix_match($match) : new_null();
  }
  function pcre_match_all($regex, $subject, $options, $offset = 0) {
    $matches = array();
    \preg_match_all(
      _pcre_compose($regex, $options),
      $subject,
      $matches,
      \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE,
      $offset
    );
    return map(
      $matches,
      function($match) {
        return _pcre_fix_match($match);
      }
    );
  }
  function pcre_replace(
    $regex,
    $subject,
    $replacement,
    $limit = null,
    $options = ""
  ) {
    $result = \preg_replace(
      _pcre_compose($regex, $options),
      $replacement,
      $subject,
      ($limit === null) ? (-1) : \max(0, $limit)
    );
    _pcre_check_last_error();
    if (!\is_string($result)) {
      throw new PCREException("preg_replace() failed");
    }
    return $result;
  }
  function pcre_split($regex, $subject, $limit = null, $options = "") {
    $pieces = \preg_split(
      _pcre_compose($regex, $options),
      $subject,
      ($limit === null) ? (-1) : max(1, $limit)
    );
    _pcre_check_last_error();
    if (!\is_array($pieces)) {
      throw new PCREException("preg_split() failed");
    }
    return $pieces;
  }
  final class PCREException extends \Exception {}
  function _pcre_compose($regex, $options = "") {
    return "/"._EscapeCache::escape($regex)."/".$options;
  }
  final class _EscapeCache {
    private static $cache = array();
    public static function escape($regex) {
      $escaped = get_or_null(self::$cache, $regex);
      if ($escaped !== null) {
        return $escaped;
      }
      if (size(self::$cache) >= 10000) {
        self::$cache = array();
      }
      return self::$cache[$regex] = _pcre_escape($regex);
    }
  }
  function _pcre_escape($regex) {
    $result = "";
    $length = length($regex);
    $escape = false;
    for ($i = 0; $i < $length; $i++) {
      $char = $regex[$i];
      if ($escape) {
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
  function _pcre_fix_match($match) {
    foreach ($match as $k => $v) {
      if ($v[1] == (-1)) {
        unset($match[$k]);
      }
    }
    return $match;
  }
  function _pcre_get_error_message($error) {
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
  function _pcre_check_last_error() {
    $error = \preg_last_error();
    if ($error !== \PREG_NO_ERROR) {
      throw new PCREException(_pcre_get_error_message($error), $error);
    }
  }
}
