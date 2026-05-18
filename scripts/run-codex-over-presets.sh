#!/usr/bin/env bash

set -euo pipefail

INPUT_DIR="${INPUT_DIR:-/tmp/openapidocs-preset-input}"
OUTPUT_DIR="${OUTPUT_DIR:-/tmp/openapidocs-ai-output}"
PROMPT_FILE="${PROMPT_FILE:-}"
OVERWRITE="${OVERWRITE:-0}"

usage() {
  cat <<'EOF'
Usage:
  scripts/run-codex-over-presets.sh [--input-dir DIR] [--output-dir DIR] [--prompt-file FILE] [--overwrite]

Environment:
  CODEX_RUNNER  Required. Shell command that reads the prepared prompt from stdin and writes JSON to stdout.
                Example:
                  export CODEX_RUNNER='codex exec'

  INPUT_DIR     Default: /tmp/openapidocs-preset-input
  OUTPUT_DIR    Default: /tmp/openapidocs-ai-output
  PROMPT_FILE   Optional file whose contents will be prepended to the built-in prompt.
  OVERWRITE     Set to 1 to replace existing output files.

Notes:
  - The runner writes one JSON file per plugin into OUTPUT_DIR.
  - Each Codex invocation gets exactly one plugin input file.
  - The script expects the Codex response body to be valid JSON.
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --input-dir)
      INPUT_DIR="$2"
      shift 2
      ;;
    --output-dir)
      OUTPUT_DIR="$2"
      shift 2
      ;;
    --prompt-file)
      PROMPT_FILE="$2"
      shift 2
      ;;
    --overwrite)
      OVERWRITE=1
      shift
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage >&2
      exit 1
      ;;
  esac
done

if [[ -z "${CODEX_RUNNER:-}" ]]; then
  echo "CODEX_RUNNER is required." >&2
  exit 1
fi

if [[ ! -d "$INPUT_DIR" ]]; then
  echo "Input directory not found: $INPUT_DIR" >&2
  exit 1
fi

mkdir -p "$OUTPUT_DIR"

EXTRA_PROMPT=""
if [[ -n "$PROMPT_FILE" ]]; then
  if [[ ! -f "$PROMPT_FILE" ]]; then
    echo "Prompt file not found: $PROMPT_FILE" >&2
    exit 1
  fi

  EXTRA_PROMPT="$(cat "$PROMPT_FILE")"
fi

build_prompt() {
  local plugin_file="$1"
  local plugin_name="$2"

  cat <<EOF
${EXTRA_PROMPT}
You are reviewing one ApiReference prompt-ready plugin summary.

Task:
- Read the JSON input below.
- Propose global-by-parameter-name preset example values for required parameters.
- Prefer stable demo-friendly values when possible.
- Reuse the same value for the same parameter name where reasonable.
- Flag collisions where one global value may be ambiguous or risky.
- If context is insufficient, mark it as uncertain instead of guessing confidently.

Return valid JSON only. No markdown fences. No prose before or after the JSON.

Required output shape:
{
  "plugin": "${plugin_name}",
  "candidate_presets": {
    "parameterName": "value"
  },
  "collisions": [
    {
      "parameter": "name",
      "reason": "why one global value is risky"
    }
  ],
  "uncertain": [
    {
      "parameter": "name",
      "reason": "why the value is uncertain"
    }
  ],
  "notes": [
    "short note"
  ]
}

Plugin input JSON:
$(cat "$plugin_file")
EOF
}

find "$INPUT_DIR" -maxdepth 1 -type f -name '*_api_method_info_prompt_ready.json' | sort | while IFS= read -r plugin_file; do
  plugin_base="$(basename "$plugin_file")"
  plugin_name="${plugin_base%_api_method_info_prompt_ready.json}"
  output_file="$OUTPUT_DIR/${plugin_name}_presets.json"

  if [[ -f "$output_file" && "$OVERWRITE" != "1" ]]; then
    echo "Skipping existing output: $output_file"
    continue
  fi

  echo "==> $plugin_name"
  prompt="$(build_prompt "$plugin_file" "$plugin_name")"

  if ! printf '%s\n' "$prompt" | sh -lc "$CODEX_RUNNER" >"$output_file"; then
    echo "FAILED: $plugin_name" >&2
    rm -f "$output_file"
    continue
  fi
done
