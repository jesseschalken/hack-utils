<?hh // strict

namespace HackUtils;

type fn0<+T> = (function(): T);
type fn1<-T1, +T> = (function(T1): T);
type fn2<-T1, -T2, +T> = (function(T1, T2): T);
type fn3<-T1, -T2, -T3, +T> = (function(T1, T2, T3): T);
