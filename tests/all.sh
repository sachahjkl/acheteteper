#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname "$0")/.." && pwd)
php "$root/tests/run.php"
"$root/tests/http.sh"
