#!/bin/sh
set -eu

compose_file="test-harness/docker-compose.yml"
plugin_path="/var/www/html/wp-content/plugins/wordpress-sso-provider/test-harness"
result_dir="$(mktemp -d)"
trap 'rm -rf "$result_dir"' EXIT INT TERM

docker compose -f "$compose_file" exec -T cli \
    wp --allow-root --path=/var/www/html eval-file "$plugin_path/concurrency-setup.php"

docker compose -f "$compose_file" exec -T cli \
    wp --allow-root --path=/var/www/html eval-file "$plugin_path/concurrency-consume.php" >"$result_dir/first" &
first_pid=$!
docker compose -f "$compose_file" exec -T cli \
    wp --allow-root --path=/var/www/html eval-file "$plugin_path/concurrency-consume.php" >"$result_dir/second" &
second_pid=$!

wait "$first_pid"
wait "$second_pid"

consumed="$(grep -h -c '^CONSUMED$' "$result_dir/first" "$result_dir/second" | awk '{total += $1} END {print total}')"
rejected="$(grep -h -c '^REJECTED$' "$result_dir/first" "$result_dir/second" | awk '{total += $1} END {print total}')"

if [ "$consumed" -ne 1 ] || [ "$rejected" -ne 1 ]; then
    echo "Concurrency test failed: consumed=$consumed rejected=$rejected" >&2
    cat "$result_dir/first" "$result_dir/second" >&2
    exit 1
fi

echo 'Authorization-code concurrency test passed: exactly one consumer succeeded.'

