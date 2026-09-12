#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
(( EUID == 0 )) || { printf 'restore must run as root\n' >&2; exit 1; }
(( $# >= 2 && $# <= 3 )) || { printf 'usage: %s {staging|production} BACKUP.tar.gz [CHECKSUM]\n' "$0" >&2; exit 2; }
namespace="$1"
archive="$(realpath -- "$2")"
checksum="$(realpath -- "${3:-$2.sha256}")"
cd "$(dirname -- "$archive")"
sha256sum --check "$(basename -- "$checksum")"
temporary="$(mktemp -d)"
trap 'rm -rf -- "$temporary"' EXIT
tar -xzf "$archive" -C "$temporary"
test "$(sqlite3 "$temporary/database.db" 'pragma integrity_check;')" = ok
volume="acheteteper-${namespace}-data"
path="$(nomad volume status -namespace "$namespace" -json "$volume" | jq -r .HostPath)"
[[ "$path" == /data/Services/nomad/volumes/* ]] || { printf 'unexpected volume path\n' >&2; exit 1; }
status="$(nomad job status -namespace "$namespace" -json acheteteper 2>/dev/null | jq -r '.[0].Status' || true)"
[[ -z "$status" || "$status" == dead ]] || { printf 'stop the target job before restoration\n' >&2; exit 1; }
fuser "$path/database.db" >/dev/null 2>&1 && { printf 'wait for allocations to release SQLite\n' >&2; exit 1; }
rm -f -- "$path/database.db" "$path/database.db-wal" "$path/database.db-shm"
rm -rf -- "$path/uploads"
install -o 65534 -g 65534 -m 0600 "$temporary/database.db" "$path/database.db"
cp -a "$temporary/uploads" "$path/uploads"
chown -R 65534:65534 "$path/uploads"
