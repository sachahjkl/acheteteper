#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
(( EUID == 0 )) || { printf 'backup must run as root\n' >&2; exit 1; }
(( $# == 2 )) || { printf 'usage: %s {staging|production} BACKUP.tar.gz\n' "$0" >&2; exit 2; }
[[ "$1" == staging || "$1" == production ]] || { printf 'invalid namespace\n' >&2; exit 2; }
namespace="$1"
archive="$(realpath -m -- "$2")"
volume="acheteteper-${namespace}-data"
path="$(nomad volume status -namespace "$namespace" -json "$volume" | jq -r .HostPath)"
[[ "$path" == /data/Services/nomad/volumes/* ]] || { printf 'unexpected volume path\n' >&2; exit 1; }
temporary="$(mktemp -d)"
trap 'rm -rf -- "$temporary"' EXIT
sqlite3 "$path/database.db" ".backup '$temporary/database.db'"
test "$(sqlite3 "$temporary/database.db" 'pragma integrity_check;')" = ok
cp -a "$path/uploads" "$temporary/uploads"
tar -czf "$archive" -C "$temporary" database.db uploads
printf '%s  %s\n' "$(sha256sum "$archive" | cut -d' ' -f1)" "$(basename -- "$archive")" >"$archive.sha256"
chmod 0600 "$archive" "$archive.sha256"
