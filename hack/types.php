<?hh // strict

namespace HackUtils;

type map<T> = array<arraykey, T>;
type vector<T> = array<T>;
type set = array<arraykey, mixed>;
type intmap<T> = array<int, T>;
type intset = array<int, mixed>;
type datetime = datetime\datetime;
type timezone = datetime\timezone;
