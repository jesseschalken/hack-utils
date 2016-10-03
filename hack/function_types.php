<?hh // strict

namespace HackUtils;

type fun0<+T> = (function(): T);
type fun1<-T1, +T> = (function(T1): T);
type fun2<-T1, -T2, +T> = (function(T1, T2): T);
type fun3<-T1, -T2, -T3, +T> = (function(T1, T2, T3): T);
