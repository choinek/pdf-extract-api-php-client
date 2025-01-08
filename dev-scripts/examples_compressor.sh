#!/bin/bash

SCRIPT_DIR=$(dirname "$(realpath "$0")")
SOURCE_DIR="$SCRIPT_DIR/example_sources"
OUTPUT_DIR="$SCRIPT_DIR/../examples/assets"

echo "Watching $SOURCE_DIR for changes to *.html, *.pdf, *.png files. Press Ctrl+C to stop."

trap "echo 'Exiting...'; exit 0" SIGINT

while inotifywait -e modify "$SOURCE_DIR"/*.html "$SOURCE_DIR"/*.pdf "$SOURCE_DIR"/*.png; do
   for FILE in "$SOURCE_DIR"/*.html "$SOURCE_DIR"/*.pdf "$SOURCE_DIR"/*.png; do
      BASENAME=$(basename "$FILE" | sed 's/_template//')
      OUTPUT_FILE="$OUTPUT_DIR/${BASENAME}.gz"
      gzip -c -9 "$FILE" >"$OUTPUT_FILE"
      echo "Compressed $FILE to $OUTPUT_FILE"
   done
done


