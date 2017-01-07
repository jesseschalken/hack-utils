<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class StrictErrors {
    private static $handler;
    public static function start() {
      \set_error_handler(
        (self::$handler !== null)
          ? self::$handler
          : (self::$handler = function($type, $message, $file, $line) {
               throw new \ErrorException($message, 0, $type, $file, $line);
             })
      );
      return new self();
    }
    private $bound = true;
    private function __construct() {}
    public function __destruct() {
      $this->finish();
    }
    public function finish() {
      if (\hacklib_cast_as_boolean($this->bound)) {
        \restore_error_handler();
        $this->bound = false;
      }
    }
    public function finishAny($x) {
      $this->finish();
      return $x;
    }
    public function finishZero($x) {
      return $this->finishAny(Exception::assertZero($x));
    }
    public function finishArray($x) {
      return $this->finishAny(Exception::assertArray($x));
    }
    public function finishString($x) {
      return $this->finishAny(Exception::assertString($x));
    }
    public function finishInt($x) {
      return $this->finishAny(Exception::assertInt($x));
    }
    public function finishTrue($x) {
      return $this->finishAny(Exception::assertTrue($x));
    }
    public function finishResource($x) {
      return $this->finishAny(Exception::assertResource($x));
    }
    public function finishBool($x) {
      return $this->finishAny(Exception::assertBool($x));
    }
  }
}
