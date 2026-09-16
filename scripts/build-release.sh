#!/bin/sh
set -eu

project_root="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
version="0.1.0"
artifact="$project_root/build/wordpress-sso-provider-$version.zip"
checksum="$artifact.sha256"
stage="$(mktemp -d)"
plugin="$stage/wordpress-sso-provider"
trap 'rm -rf "$stage"' EXIT INT TERM

mkdir -p "$plugin/admin/build" "$plugin/docs"

rsync -a "$project_root/src" "$plugin/"
rsync -a "$project_root/admin/build/" "$plugin/admin/build/"
rsync -a "$project_root/docs/" "$plugin/docs/"
rsync -a "$project_root/vendor" "$plugin/"
cp "$project_root/wordpress-sso-provider.php" "$plugin/"
cp "$project_root/uninstall.php" "$plugin/"
cp "$project_root/composer.json" "$plugin/"
cp "$project_root/composer.lock" "$plugin/"
cp "$project_root/README.md" "$plugin/"
cp "$project_root/LICENSE" "$plugin/"

composer install \
    --working-dir="$plugin" \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --classmap-authoritative \
    --no-progress

# Composer packages sometimes ship their own CI and test fixtures. They are not
# runtime dependencies and must not enter the plugin artifact.
find "$plugin/vendor" -type d \( \
    -name .git -o -name .github -o -name tests -o -name Tests -o -name test -o -name test-harness \
\) -prune -exec rm -rf {} +

find "$plugin" -exec touch -t 202609160000 {} +
rm -f "$artifact" "$checksum"

(
    cd "$stage"
    find wordpress-sso-provider -type f -print | LC_ALL=C sort | zip -X -q "$artifact" -@
)

if zipinfo -1 "$artifact" | grep -Eq '(^|/)(tests|test-harness|node_modules|\.git|\.github|\.phpunit\.cache)(/|$)'; then
    echo 'Release archive contains a prohibited development path.' >&2
    exit 1
fi

shasum -a 256 "$artifact" > "$checksum"
echo "$artifact"
cat "$checksum"
