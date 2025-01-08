#!/bin/bash

SCRIPT_DIR=$(dirname "$(realpath "$0")")
SOURCE_DIR="$SCRIPT_DIR/example_sources"
OUTPUT_DIR="$SCRIPT_DIR/../examples/assets"

echo "Watching $SOURCE_DIR for changes to *.pdf, *.png files. Press Ctrl+C to stop."
trap "echo 'Exiting...'; exit 0" SIGINT

shopt -s nullglob

while inotifywait -e modify "$SOURCE_DIR"/*.pdf "$SOURCE_DIR"/*.png; do
   for FILE in "$SOURCE_DIR"/*.pdf "$SOURCE_DIR"/*.png; do
      BASENAME=$(basename "$FILE" | sed 's/_template//')
      OUTPUT_FILE="$OUTPUT_DIR/$BASENAME.gz"
      gzip -c -9 "$FILE" >"$OUTPUT_FILE"
      echo "Compressed $FILE to $OUTPUT_FILE"
      rm -f "$OUTPUT_DIR/$BASENAME"
      echo "Removed $OUTPUT_DIR/$BASENAME"
      cp "$FILE" "$OUTPUT_DIR/$BASENAME"
      echo "Copied $FILE to $OUTPUT_DIR/$BASENAME"
   done
done


