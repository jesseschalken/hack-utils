<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class _Cache {
    public static $string;
    public static $float;
    public static $int;
    public static $num;
    public static $arraykey;
    public static $bool;
    public static $mixed;
    public static $resource;
    public static $mixedArray;
    public static $mixedAssoc;
  }
  function assert_string() {
    return
      \hacklib_cast_as_boolean(_Cache::$string) ?: (_Cache::$string =
                                                      function($x) {
                                                        return
                                                          \hacklib_cast_as_boolean(
                                                            \is_string($x)
                                                          )
                                                            ? $x
                                                            : _type_error(
                                                              $x,
                                                              "string"
                                                            );
                                                      });
  }
  function assert_float() {
    return
      \hacklib_cast_as_boolean(_Cache::$float) ?: (_Cache::$float =
                                                     function($x) {
                                                       return
                                                         \hacklib_cast_as_boolean(
                                                           \is_float($x)
                                                         )
                                                           ? $x
                                                           : _type_error(
                                                             $x,
                                                             "float"
                                                           );
                                                     });
  }
  function assert_int() {
    return
      \hacklib_cast_as_boolean(_Cache::$int) ?: (_Cache::$int =
                                                   function($x) {
                                                     return
                                                       \hacklib_cast_as_boolean(
                                                         \is_int($x)
                                                       )
                                                         ? $x
                                                         : _type_error(
                                                           $x,
                                                           "int"
                                                         );
                                                   });
  }
  function assert_bool() {
    return
      \hacklib_cast_as_boolean(_Cache::$bool) ?: (_Cache::$bool =
                                                    function($x) {
                                                      return
                                                        \hacklib_cast_as_boolean(
                                                          \is_bool($x)
                                                        )
                                                          ? $x
                                                          : _type_error(
                                                            $x,
                                                            "bool"
                                                          );
                                                    });
  }
  function assert_resource() {
    return
      \hacklib_cast_as_boolean(_Cache::$resource) ?: (_Cache::$resource =
                                                        function($x) {
                                                          return
                                                            \hacklib_cast_as_boolean(
                                                              \is_resource(
                                                                $x
                                                              )
                                                            )
                                                              ? $x
                                                              : _type_error(
                                                                $x,
                                                                "bool"
                                                              );
                                                        });
  }
  function assert_null() {
    return function($x) {
      return ($x === null) ? null : _type_error($x, "null");
    };
  }
  function assert_nullable($t) {
    return function($x) use ($t) {
      return ($x === null) ? null : $t($x);
    };
  }
  function assert_array($t) {
    return function($x) use ($t) {
      $x =
        (\hacklib_cast_as_boolean(\is_array($x)) &&
         \hacklib_cast_as_boolean(is_vector($x)))
          ? $x
          : _type_error($x, "array (vector-like)");
      return map($x, $t);
    };
  }
  function assert_assoc($ak, $av) {
    return function($x) use ($ak, $av) {
      $x =
        \hacklib_cast_as_boolean(\is_array($x))
          ? $x
          : _type_error($x, "array (map-like)");
      $r = array();
      foreach ($x as $k => $v) {
        $r[$ak($k)] = $av($v);
      }
      return $r;
    };
  }
  function assert_object($class) {
    return function($x) use ($class) {
      return \hacklib_instanceof($x, $class) ? $x : _type_error($x, $class);
    };
  }
  function assert_num() {
    return
      \hacklib_cast_as_boolean(_Cache::$num) ?: (_Cache::$num =
                                                   function($x) {
                                                     if (\hacklib_cast_as_boolean(
                                                           \is_float($x)
                                                         )) {
                                                       return $x;
                                                     }
                                                     if (\hacklib_cast_as_boolean(
                                                           \is_int($x)
                                                         )) {
                                                       return $x;
                                                     }
                                                     return _type_error(
                                                       $x,
                                                       "num (int|float)"
                                                     );
                                                   });
  }
  function assert_arraykey() {
    return
      \hacklib_cast_as_boolean(_Cache::$arraykey) ?: (_Cache::$arraykey =
                                                        function($x) {
                                                          if (\hacklib_cast_as_boolean(
                                                                \is_string(
                                                                  $x
                                                                )
                                                              )) {
                                                            return $x;
                                                          }
                                                          if (\hacklib_cast_as_boolean(
                                                                \is_int(
                                                                  $x
                                                                )
                                                              )) {
                                                            return $x;
                                                          }
                                                          return
                                                            _type_error(
                                                              $x,
                                                              "arraykey (int|string)"
                                                            );
                                                        });
  }
  function assert_mixed() {
    return \hacklib_cast_as_boolean(_Cache::$mixed) ?: (_Cache::$mixed =
                                                          function($x) {
                                                            return $x;
                                                          });
  }
  function assert_mixed_array() {
    return
      \hacklib_cast_as_boolean(_Cache::$mixedArray) ?: (_Cache::$mixedArray =
                                                          assert_array(
                                                            assert_mixed()
                                                          ));
  }
  function assert_mixed_assoc() {
    return
      \hacklib_cast_as_boolean(_Cache::$mixedAssoc) ?: (_Cache::$mixedAssoc =
                                                          assert_assoc(
                                                            assert_arraykey(),
                                                            assert_mixed()
                                                          ));
  }
  function assert_shape($f) {
    $mixedAssoc = assert_mixed_assoc();
    return function($x) use ($f, $mixedAssoc) {
      return $f($mixedAssoc($x));
    };
  }
  function assert_pair($a, $b) {
    return function($x) use ($a, $b) {
      return
        (\hacklib_cast_as_boolean(\is_array($x)) &&
         \hacklib_equals(\count($x), 2) &&
         \hacklib_cast_as_boolean(is_vector($x)))
          ? array($a($x[0]), $b($x[1]))
          : _type_error($x, "pair (vector array of length 2)");
    };
  }
  function _typeof($x) {
    if (\hacklib_cast_as_boolean(\is_int($x))) {
      return "int";
    }
    if (\hacklib_cast_as_boolean(\is_string($x))) {
      return "string";
    }
    if (\hacklib_cast_as_boolean(\is_null($x))) {
      return "void";
    }
    if (\hacklib_cast_as_boolean(\is_float($x))) {
      return "float";
    }
    if (\hacklib_cast_as_boolean(\is_object($x))) {
      return \get_class($x);
    }
    if (\hacklib_cast_as_boolean(\is_bool($x))) {
      return "bool";
    }
    if (\hacklib_cast_as_boolean(\is_resource($x))) {
      return "resource";
    }
    if (\hacklib_cast_as_boolean(\is_array($x))) {
      $l = \count($x);
      $i = 0;
      foreach ($x as $k => $v) {
        if ($k !== ($i++)) {
          return "array (map-like, size = ".$l.")";
        }
      }
      return "array (vector-like, length = ".$l.")";
    }
    throw new \Exception("unreachable");
  }
  function _type_error($x, $type) {
    throw new AssertionFailed("Expected ".$type.", got "._typeof($x));
  }
  class AssertionFailed extends \Exception {}
}
