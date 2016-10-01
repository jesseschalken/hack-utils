# hack-utils

Type safe wrappers for PHP functions for usage with Hack. The library is
designed to work smoothly with stock PHP via `h2tp`, all the way back to PHP 5.3
(which still has security updates in Ubuntu 12.04).

In order for code and libraries written using this library to feel native to
PHP developers, the use of standard PHP arrays is preferred over Hack's
Collections (`Map`, `Vector`, `Set`...).
