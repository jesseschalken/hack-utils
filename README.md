# hack-utils

Type safe wrappers for PHP functions for usage with [Hack](http://hacklang.org/), with the following objectives:

- Be completely and accurately statically typed
- Interoperate smoothly with PHP >= 5.3 via `h2tp <src> <dst> --no-collections`
- Use PHP arrays in place of Hack collections so the library is natural to use from PHP
- Where possible, provide simpler or more useful semantics than stock PHP APIs
- Cover the core set of functionality that common Hack/PHP programs require

The library functions like any other Composer package and does not require `hhvm` or `h2tp` to be installed to be used. Use `./build.bash` to rebuild the PHP code using `h2tp`.

Functions are provided for
- [strings](./hack/main.php) _(find, repeat, replace, slice, splice, chunk, trim, pad, reverse, split, join, to/from hexadecimal, encode/decode utf8, ...)_
- [arrays](./hack/main.php), both associative and sequentially indexed _(concat, push, pop, shift, unshift, range, filter, map, reduce, group-by, combine, flip, transpose, union, intersect, diff, select, zip, unzip, reverse, shuffle, chunk, repeat, slice, splice, find, sort, ...)_
- [ints/floats](./hack/math.php) _(max, min, abs, ceil, floor, trunc, round, signbit, exp, pow, log, sum, product, sin, cos, tan, sinh, ...)_
- [Date/Time](./hack/DateTime.php) _(parse, format, get parts, from parts, ...)_
- [JSON](./hack/json.php) _(encode, decode)_
- [PCRE regular expressions](./hack/pcre.php) _(match, match all, split, replace, quote)_

Browse the source code in the [`hack/`](./hack/) directory.

Functions have simple verbal names. Where a different use of the same verb is needed for different types (sequential array, associative array, string), the less common versions are suffixed with `_string`, `_array` or `_assoc` as appropriate. For example, `chunk` (for arrays), `chunk_string`, `chunk_assoc`.

Symbols prefixed with `_` should not be referenced from outside the library.

TODO:
- Filesystem (fopen, scandir, unlink, ...)
- Command execution (proc_open, ...)
