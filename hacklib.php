<?php

function hacklib_cast_as_boolean($x) {
    return (bool)$x;
}

function hacklib_equals($a, $b) {
    return $a == $b;
}

function hacklib_not_equals($a, $b) {
    return $a != $b;
}

function hacklib_id($x) {
    return $x;
}

function hacklib_instanceof($x, $class) {
    return $x instanceof $class;
}

function hacklib_nullsafe($v) {
    return $v === null ? new __HackLibNullObj() : $v;
}

class __HackLibNullObj {
    function __call($method, $arguments) {
        return null;
    }
    function __get($prop) {
        return null;
    }
}

