<?hh // strict

namespace HackUtils;

type list<T> = array<T>;
type map<T> = array<mapkey, T>;
type mapkey = arraykey;
type setvalue = arraykey;
type set = map<mixed>;
type str = string;
