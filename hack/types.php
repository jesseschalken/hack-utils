<?hh // strict

namespace HackUtils;

type datetime = datetime\datetime;
type timezone = datetime\timezone;

type assoc<+T> = array<arraykey, T>;

type fn0<+T> = (function(): T);
type fn1<-T1, +T> = (function(T1): T);
type fn2<-T1, -T2, +T> = (function(T1, T2): T);
type fn3<-T1, -T2, -T3, +T> = (function(T1, T2, T3): T);
type fn4<-T1, -T2, -T3, -T4, +T> = (function(T1, T2, T3, T4): T);
type fn5<-T1, -T2, -T3, -T4, -T5, +T> = (function(T1, T2, T3, T4, T5): T);
