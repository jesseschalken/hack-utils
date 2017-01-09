<?php
namespace HackUtils {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  abstract class Errors {
    public static function start() {
      return new static();
    }
    private $bound = false;
    public function __construct() {
      $self = $this;
      $func = function($type, $message, $file, $line) use ($self) {
        $e = new \ErrorException($message, 0, $type, $file, $line);
        $self->handleError($e);
      };
      \set_error_handler($func);
      $this->bound = true;
    }
    public function __destruct() {
      $this->finish();
    }
    public abstract function handleError($e);
    public final function finish() {
      if (\hacklib_cast_as_boolean($this->bound)) {
        \restore_error_handler();
        $this->bound = false;
      }
    }
    public final function finishAny($x) {
      $this->finish();
      return $x;
    }
    public final function finishZero($x) {
      return $this->finishAny(Exception::assertZero($x));
    }
    public final function finishArray($x) {
      return $this->finishAny(Exception::assertArray($x));
    }
    public final function finishString($x) {
      return $this->finishAny(Exception::assertString($x));
    }
    public final function finishInt($x) {
      return $this->finishAny(Exception::assertInt($x));
    }
    public final function finishTrue($x) {
      return $this->finishAny(Exception::assertTrue($x));
    }
    public final function finishResource($x) {
      return $this->finishAny(Exception::assertResource($x));
    }
    public final function finishBool($x) {
      return $this->finishAny(Exception::assertBool($x));
    }
  }
  final class StrictErrors extends Errors {
    public function handleError($e) {
      throw $e;
    }
  }
  final class IgnoreErrors extends Errors {
    public function handleError($e) {}
  }
  final class CaptureErrors extends Errors {
    private $errors = array();
    public function handleError($e) {
      $this->errors[] = $e;
    }
    public function getErrors() {
      return $this->errors;
    }
  }
}
