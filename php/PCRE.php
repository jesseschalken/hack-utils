<?php
namespace HackUtils\PCRE {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \HackUtils as HU;
  final class Pattern {
    public static function quote($text) {
      return \preg_quote($text);
    }
    public static function create($regex, $options = "") {
      return new self($regex, $options);
    }
    private static $escapeCache = array();
    private static function escape($regex) {
      $escaped = HU\get_or_null(self::$escapeCache, $regex);
      if ($escaped !== null) {
        return $escaped;
      }
      if (\count(self::$escapeCache) >= 10000) {
        self::$escapeCache = array();
      }
      return self::$escapeCache[$regex] = self::escape($regex);
    }
    private static function escapeImpl($regex) {
      $result = "";
      $length = \strlen($regex);
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
    private static
      $errors = array(
        \PREG_NO_ERROR => "No errors",
        \PREG_INTERNAL_ERROR => "Internal PCRE error",
        \PREG_BACKTRACK_LIMIT_ERROR =>
          "Backtrack limit (pcre.backtrack_limit) was exhausted",
        \PREG_RECURSION_LIMIT_ERROR =>
          "Recursion limit (pcre.recursion_limit) was exhausted",
        \PREG_BAD_UTF8_ERROR => "Malformed UTF-8 data",
        \PREG_BAD_UTF8_OFFSET_ERROR =>
          "The offset didn't correspond to the beginning of a valid UTF-8 code point",
        6 => "JIT stack space limit exceeded"
      );
    private static function checkLastError() {
      $error = \preg_last_error();
      if ($error !== \PREG_NO_ERROR) {
        $message = HU\get_or_default(self::$errors, $error, "Unknown error");
        throw new Exception($message, $error);
      }
    }
    private $composed;
    private function __construct($regex, $options = "") {
      $this->composed = "/".self::escape($regex)."/".$options;
    }
    public function match($subject, $offset = 0) {
      $match = array();
      $flags = \PREG_OFFSET_CAPTURE;
      $count =
        \preg_match($this->composed, $subject, $match, $flags, $offset);
      self::checkLastError();
      return $count ? (new Match($match)) : HU\new_null();
    }
    public function matchOrThrow($subject, $offset = 0) {
      $match = $this->match($subject, $offset);
      if (!$match) {
        throw new NoMatchException(
          "Failed to match ".
          $this->composed.
          " against string '".
          $subject.
          "'"
        );
      }
      return $match;
    }
    public function matchAll($subject, $offset = 0) {
      $matches = array();
      $flags = \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE;
      \preg_match_all($this->composed, $subject, $matches, $flags, $offset);
      return HU\map(
        $matches,
        function($match) {
          return new Match($match);
        }
      );
    }
    public function replace($subject, $replacement, $limit = null) {
      $result = \preg_replace(
        $this->composed,
        $replacement,
        $subject,
        ($limit === null) ? (-1) : HU\max(0, $limit)
      );
      self::checkLastError();
      if (!\is_string($result)) {
        throw new Exception("preg_replace() failed");
      }
      return $result;
    }
    public function split($subject, $limit = null) {
      $pieces = \preg_split(
        $this->composed,
        $subject,
        ($limit === null) ? (-1) : max(1, $limit)
      );
      self::checkLastError();
      if (!\is_array($pieces)) {
        throw new Exception("preg_split() failed");
      }
      return $pieces;
    }
  }
  final class Match {
    private $match;
    public function __construct($match) {
      $this->match = $match;
      foreach ($this->match as $k => $v) {
        if ($v[1] == (-1)) {
          unset($this->match[$k]);
        }
      }
    }
    public function get($group = 0) {
      return $this->match[$group][0];
    }
    public function getOrNull($group = 0) {
      $match = HU\get_or_null($this->match, $group);
      return $match ? $match[0] : HU\new_null();
    }
    public function getOrEmpty($group = 0) {
      $match = HU\get_or_null($this->match, $group);
      return $match ? $match[0] : "";
    }
    public function start($group = 0) {
      return $this->match[$group][1];
    }
    public function end($group = 0) {
      list($text, $offset) = $this->match[$group];
      return $offset + \strlen($text);
    }
    public function range($group = 0) {
      list($text, $offset) = $this->match[$group];
      return array($offset, $offset + \strlen($text));
    }
    public function length($group = 0) {
      return \strlen($this->get($group));
    }
    public function has($group) {
      return HU\key_exists($this->match, $group);
    }
    public function __toString() {
      return $this->get();
    }
    public function toString() {
      return $this->get();
    }
    public function toArray() {
      return HU\map_assoc(
        $this->match,
        function($x) {
          return $x[0];
        }
      );
    }
  }
  class Exception extends \Exception {}
  class NoMatchException extends Exception {}
}
