<?php
namespace HackUtils\ctype {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  function isalnum($s, $i = 0) {
    return \ctype_alnum(_char($s, $i));
  }
  function isblank($s, $i = 0) {
    $c = _char($s, $i);
    return ($c === "\t") || ($c === " ");
  }
  function isapha($s, $i = 0) {
    return \ctype_alpha(_char($s, $i));
  }
  function iscntrl($s, $i = 0) {
    return \ctype_cntrl(_char($s, $i));
  }
  function isdigit($s, $i = 0) {
    return \ctype_digit(_char($s, $i));
  }
  function isgraph($s, $i = 0) {
    return \ctype_graph(_char($s, $i));
  }
  function islower($s, $i = 0) {
    return \ctype_lower(_char($s, $i));
  }
  function isprint($s, $i = 0) {
    return \ctype_print(_char($s, $i));
  }
  function ispunct($s, $i = 0) {
    return \ctype_punct(_char($s, $i));
  }
  function isspace($s, $i = 0) {
    return \ctype_space(_char($s, $i));
  }
  function isupper($s, $i = 0) {
    return \ctype_upper(_char($s, $i));
  }
  function isxdigit($s, $i = 0) {
    return \ctype_xdigit(_char($s, $i));
  }
  function _char($s, $i) {
    $l = \strlen($s);
    if ($i < 0) {
      $i += $l;
    }
    if (($i < 0) || ($i >= $l)) {
      throw new \Exception(
        "Byte offset ".$i." out of bounds in string '".$s."'"
      );
    }
    return $s[$i];
  }
}
