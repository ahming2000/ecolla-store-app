#!/usr/bin/env python3
"""Inspect committed changes since the last changing-log checkpoint."""

from __future__ import annotations

import json
import subprocess
import sys
from pathlib import Path
from typing import Any


ENGLISH_LOG = Path("docs/changing-logs.en.json")
CHINESE_LOG = Path("docs/changing-logs.zh.json")
METADATA = Path("docs/changing-logs.meta.json")


def run_git(repository: Path, *arguments: str, check: bool = True) -> str:
    result = subprocess.run(
        ["git", *arguments],
        cwd=repository,
        check=False,
        capture_output=True,
        text=True,
    )

    if check and result.returncode != 0:
        raise RuntimeError(result.stderr.strip() or "Git command failed.")

    return result.stdout.strip()


def load_json(path: Path) -> dict[str, Any]:
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except FileNotFoundError as error:
        raise RuntimeError(f"Required file is missing: {path}") from error
    except json.JSONDecodeError as error:
        raise RuntimeError(f"Invalid JSON in {path}: {error}") from error


def commit_exists(repository: Path, commit: str) -> bool:
    if not commit:
        return False

    result = subprocess.run(
        ["git", "cat-file", "-e", f"{commit}^{{commit}}"],
        cwd=repository,
        check=False,
        capture_output=True,
        text=True,
    )

    return result.returncode == 0


def is_ancestor(repository: Path, ancestor: str, descendant: str) -> bool:
    result = subprocess.run(
        ["git", "merge-base", "--is-ancestor", ancestor, descendant],
        cwd=repository,
        check=False,
        capture_output=True,
        text=True,
    )

    return result.returncode == 0


def resolve_baseline(
    repository: Path,
    head: str,
    recorded_commit: str,
) -> tuple[str, str]:
    if commit_exists(repository, recorded_commit) and is_ancestor(
        repository,
        recorded_commit,
        head,
    ):
        return "metadata", recorded_commit

    changelog_commit = run_git(
        repository,
        "log",
        "-1",
        "--format=%H",
        "--",
        ENGLISH_LOG.as_posix(),
        CHINESE_LOG.as_posix(),
        check=False,
    )

    if commit_exists(repository, changelog_commit) and is_ancestor(
        repository,
        changelog_commit,
        head,
    ):
        return "changelog-history", changelog_commit

    raise RuntimeError(
        "No reachable changing-log checkpoint was found in the current branch."
    )


def commit_details(repository: Path, commit: str) -> dict[str, Any]:
    fields = run_git(
        repository,
        "show",
        "-s",
        "--date=short",
        "--format=%H%x00%ad%x00%s%x00%b",
        commit,
    ).split("\0", maxsplit=3)
    changed_files = run_git(
        repository,
        "diff-tree",
        "--no-commit-id",
        "--name-only",
        "-r",
        commit,
    ).splitlines()

    return {
        "hash": fields[0],
        "date": fields[1],
        "subject": fields[2],
        "body": fields[3].strip(),
        "files": changed_files,
    }


def main() -> int:
    try:
        repository = Path(
            run_git(Path.cwd(), "rev-parse", "--show-toplevel")
        ).resolve()
        english_log = load_json(repository / ENGLISH_LOG)
        chinese_log = load_json(repository / CHINESE_LOG)
        metadata = load_json(repository / METADATA)

        if english_log.get("versionName") != chinese_log.get("versionName"):
            raise RuntimeError(
                "English and Chinese changing logs have different current versions."
            )

        head = run_git(repository, "rev-parse", "HEAD")
        recorded_commit = str(metadata.get("lastProcessedCommit", ""))
        baseline_source, baseline = resolve_baseline(
            repository,
            head,
            recorded_commit,
        )
        commits = run_git(
            repository,
            "rev-list",
            "--reverse",
            "--no-merges",
            f"{baseline}..{head}",
        ).splitlines()
        changing_log_status = run_git(
            repository,
            "status",
            "--short",
            "--untracked-files=all",
            "--",
            ENGLISH_LOG.as_posix(),
            CHINESE_LOG.as_posix(),
            METADATA.as_posix(),
            check=False,
        ).splitlines()
        worktree_status = run_git(
            repository,
            "status",
            "--short",
            "--untracked-files=all",
            check=False,
        ).splitlines()

        output = {
            "currentVersion": english_log.get("versionName"),
            "baselineSource": baseline_source,
            "baselineCommit": baseline,
            "recordedCommit": recorded_commit,
            "headCommit": head,
            "candidateCommits": [
                commit_details(repository, commit) for commit in commits
            ],
            "changingLogFilesDirty": changing_log_status,
            "otherWorktreeChangesPresent": any(
                line not in changing_log_status for line in worktree_status
            ),
        }

        print(json.dumps(output, ensure_ascii=False, indent=2))

        return 0
    except RuntimeError as error:
        print(f"error: {error}", file=sys.stderr)

        return 1


if __name__ == "__main__":
    raise SystemExit(main())
