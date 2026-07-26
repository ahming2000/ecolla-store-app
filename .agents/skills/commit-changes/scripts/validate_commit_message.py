#!/usr/bin/env python3
"""Validate the structural rules of a Conventional Commits 1.0.0 message."""

from __future__ import annotations

import argparse
import re
import sys
from pathlib import Path


HEADER_PATTERN = re.compile(
    r"^(?P<type>[A-Za-z][A-Za-z0-9-]*)"
    r"(?:\((?P<scope>[^()\r\n]+)\))?"
    r"(?P<breaking>!)?: (?P<description>\S.*\S|\S)$"
)
BREAKING_TOKEN_PATTERN = re.compile(r"^(BREAKING CHANGE|BREAKING-CHANGE): \S")
CASE_INSENSITIVE_BREAKING_TOKEN_PATTERN = re.compile(
    r"^(breaking change|breaking-change):",
    re.IGNORECASE,
)


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Validate a Conventional Commits 1.0.0 message.",
    )
    source = parser.add_mutually_exclusive_group(required=True)
    source.add_argument("message", nargs="?", help="Commit message to validate.")
    source.add_argument("--file", type=Path, help="Read the commit message from a file.")

    return parser.parse_args()


def load_message(arguments: argparse.Namespace) -> str:
    if arguments.file is not None:
        return arguments.file.read_text(encoding="utf-8").rstrip("\r\n")

    return arguments.message


def validate_message(message: str) -> list[str]:
    errors: list[str] = []
    lines = message.splitlines()

    if not lines or not lines[0]:
        return ["the commit message must start with a subject"]

    header_match = HEADER_PATTERN.fullmatch(lines[0])

    if header_match is None:
        errors.append(
            "the subject must match <type>[optional scope][!]: <description>",
        )
    elif header_match.group("type") != header_match.group("type").lower():
        errors.append("the type must be lowercase for repository consistency")

    if len(lines) > 1 and lines[1] != "":
        errors.append("the body or footers must begin one blank line after the subject")

    for line in lines[2:]:
        breaking_token_match = CASE_INSENSITIVE_BREAKING_TOKEN_PATTERN.match(line)

        if breaking_token_match is not None and BREAKING_TOKEN_PATTERN.match(line) is None:
            errors.append(
                "breaking-change footers must use 'BREAKING CHANGE: <description>' "
                "or 'BREAKING-CHANGE: <description>'",
            )

    return errors


def main() -> int:
    arguments = parse_arguments()

    try:
        message = load_message(arguments)
    except OSError as error:
        print(f"error: unable to read commit message: {error}", file=sys.stderr)

        return 2

    errors = validate_message(message)

    if errors:
        for error in errors:
            print(f"error: {error}", file=sys.stderr)

        return 1

    print("Valid Conventional Commit message.")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
