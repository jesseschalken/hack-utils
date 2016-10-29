# hack-utils

Type safe wrappers for PHP functions for usage with [Hack](http://hacklang.org/), with the following objectives:

- Be completely and accurately statically typed
- Interoperate smoothly with PHP >= 5.3 via `h2tp <src> <dst> --no-collections`
- Use PHP arrays in place of Hack collections so the library is natural to use from PHP
- Where possible, provide simpler or more useful semantics than stock PHP APIs
- Cover the core set of functionality that common Hack/PHP programs require

The library functions like any other Composer package and does not require `hhvm` or `h2tp` to be installed to be used. Use `./build.bash` to rebuild the PHP code using `h2tp`.

Functions are provided for
- strings (find, repeat, replace, slice, splice, chunk, trim, pad, reverse, split, join, to/from hexadecimal, encode/decode utf8, ...)
- arrays, both associative and sequentially indexed (concat, push, pop, shift, unshift, range, filter, map, reduce, group-by, combine, flip, transpose, union, intersect, diff, select, zip, unzip, reverse, shuffle, chunk, repeat, slice, splice, find, sort, ...)
- ints/floats (max, min, abs, ceil, floor, trunc, round, signbit, exp, pow, log, sum, product, sin, cos, tan, sinh, ...)
- Date/Time (parse, format, get parts, from parts, ...)
- JSON (encode, decode)
- PCRE regular expressions (match, match all, split, replace, quote)

Functions have simple verbal names. Where a different use of the same verb is needed for different types (sequential array, associative array, string), the less common versions are suffixed with `_string`, `_array` or `_assoc` as appropriate. For example, `chunk` (for arrays), `chunk_string`, `chunk_assoc`.

Browse the source code in the `hack/` directory.

TODO:
- Filesystem (fopen, scandir, unlink, ...)
- Command execution (proc_open, ...)
