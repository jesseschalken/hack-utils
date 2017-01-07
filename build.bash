#!/bin/bash
set -e
cd "$(dirname "$0")"
rm -rf ./php
h2tp ./hack ./php
#find ./php -name '*.php' -exec sed -i -e '/^\s*use \\HH\\HACKLIB_ENUM_LIKE;$/d' {} \;
