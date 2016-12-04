# hack-utils

Type safe wrappers for PHP functions for usage with [Hack](http://hacklang.org/), with the following objectives:

- Be completely and accurately statically typed
- Interoperate smoothly with PHP >= 5.3 via `h2tp <src> <dst> --no-collections`
- Prefer PHP arrays over Hack collections so the library is natural to use from PHP
- Where possible, provide simpler or more useful semantics than stock PHP APIs
  - Throw exceptions in the case of error instead of returning `false` or requiring the caller to check a `_last_error()` function
  - Use a consistent parameter order with the subject (`$this` if the function were a method) as the first parameter
- Cover the core set of functionality that common Hack/PHP programs require

The library functions like any other Composer package and does not require `hhvm` or `h2tp` to be installed to be used. When modifying this library, rebuild the PHP code with [`./build.bash`](./build.bash).

Functions are provided for
- [strings](./hack/main.php) _(find, repeat, replace, slice, splice, chunk, trim, pad, reverse, split, join, [is_alnum](./hack/ctype.php), ..., to/from hex, encode/decode utf8, ...)_
- [arrays](./hack/main.php), both associative and sequentially indexed _(concat, push, pop, shift, unshift, range, filter, map, reduce, concat_map, group-by, combine, flip, transpose, union, intersect, diff, select, zip, unzip, reverse, shuffle, chunk, repeat, slice, splice, find, [sort](./hack/sort.php), ...)_
- [ints/floats](./hack/math.php) _(max, min, abs, ceil, floor, trunc, frac, round, signbit, exp, pow, log, sum, product, sin, cos, tan, sinh, ...)_
- [Date/Time](./hack/DateTime.php) _(parse, format, get parts, from parts, ...)_
- [JSON](./hack/json.php) _(encode, decode)_
- [PCRE regular expressions](./hack/PCRE.php) _(match, match all, split, replace, quote)_

Browse the source code in the [`hack/`](./hack/) directory.

Functions have simple verbal names. Where a different use of the same verb is needed for different types (sequential array, associative array, string), the less common versions are suffixed with `_string`, `_array` or `_assoc` as appropriate. For example, `chunk` (for arrays), `chunk_string`, `chunk_assoc`.

Symbols prefixed with `_` are private and should not be referenced from outside the library.

TODO:
- Filesystem (fopen, scandir, unlink, ...)
- Command execution (proc_open, ...)
