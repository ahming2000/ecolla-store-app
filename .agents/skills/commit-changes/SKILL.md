---
name: commit-changes
description: Inspect a Git worktree, identify changes owned by the current task, split them into the smallest coherent commits, and create Conventional Commits 1.0.0 messages without pushing. Use whenever the user asks to commit changes, create commits, save work to Git, separate work into commits, use Conventional Commits, or commit and optionally push completed work.
---

# Commit Changes

Create safe, focused commits from changes owned by the current task. Treat committing as authorized when this skill is explicitly invoked or the user asks to commit; do not ask for routine confirmation after a safe commit plan is clear.

## Non-negotiable boundaries

- Commit only changes attributable to the current task or explicitly included by the user.
- Treat changes that existed before the current task, unrelated edits, and uncertain hunks as user-owned. Leave them untouched and uncommitted.
- Do not infer ownership from file timestamps, Git author identity, or file location. Establish it from the current task, the session's edits, and the actual diff.
- Stop and ask one concise question when ownership cannot be determined or an owned hunk cannot be isolated safely.
- Do not edit source files merely to make a commit cleaner. This skill inspects, stages, validates, and commits existing work.
- Do not discard, reset, restore, stash, or overwrite user changes.
- Do not amend, rebase, or rewrite existing commits unless the user explicitly requests it.
- Do not bypass hooks with `--no-verify`.
- Do not push unless the user explicitly asks for a push in the current request.

## Workflow

### 1. Inspect repository state

Run:

```bash
git status --short
git branch --show-current
git diff --stat
git diff
git diff --cached --stat
git diff --cached
```

Also inspect relevant untracked files individually. Check recent subjects with `git log -10 --pretty=format:'%s'` for useful scope vocabulary, but prefer valid Conventional Commits over an inconsistent local history.

Never assume an already staged change belongs to the current task.

### 2. Classify ownership

Build an internal inventory of every changed path and, when a path mixes concerns, every changed hunk:

- **Owned:** created or changed for the current user request during this task.
- **Explicitly included:** pre-existing work the user clearly asked to commit.
- **Unowned:** pre-existing, unrelated, generated unexpectedly, secret-bearing, or otherwise outside the task.
- **Uncertain:** cannot be attributed confidently.

Proceed only with owned and explicitly included changes. Mention unowned changes in the final summary without exposing secret contents.

### 3. Plan atomic commits

Split changes as much as possible while keeping each commit coherent and usable:

- Separate independent features, fixes, refactors, documentation, configuration, tests, and maintenance work.
- Separate unrelated changes even when they occur in the same file by staging hunks selectively.
- Keep implementation with the tests that prove that implementation.
- Keep a required migration, schema update, generated lockfile, or supporting configuration with the change that depends on it.
- Avoid commits that knowingly leave the repository in a broken intermediate state.
- Do not split cosmetic fragments from the behavior they directly support.

Order prerequisite commits before dependent commits. Do not ask the user to approve each group unless the grouping materially changes their intent.

### 4. Stage precisely

Stage explicit owned paths with `git add -- <path>...`. Use `git add -p -- <path>` when only some hunks in a file belong to a commit.

Do not use blanket staging commands such as `git add .`, `git add -A`, or `git commit -a` when any unowned or uncertain change exists.

After staging each group, verify its exact contents:

```bash
git diff --cached --name-status
git diff --cached
git diff --cached --check
```

If unrelated changes were already staged, preserve them. Use path-limited commits only when owned changes are cleanly separated by whole paths and verify the resulting commit carefully. If staged ownership overlaps within a file or safe isolation is uncertain, stop and ask rather than altering the user's index.

Check for secrets, credentials, local environment files, debug artifacts, and unexpectedly large or generated files before committing.

### 5. Write a Conventional Commit

Follow [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/) exactly:

```text
<type>[optional scope][!]: <description>

[optional body]

[optional footer(s)]
```

Choose the type from the intent of the staged change:

- `feat` for a new feature.
- `fix` for a bug fix.
- `docs` for documentation only.
- `style` for formatting with no behavior change.
- `refactor` for restructuring with no feature or bug fix.
- `perf` for a performance improvement.
- `test` for test-only work.
- `build` for build system or dependency changes.
- `ci` for CI configuration or scripts.
- `chore` for maintenance not covered above.
- `revert` for reverting earlier commits.

Other noun types are allowed by the specification but use them only when the repository already defines them or the user requests them.

Use an optional noun scope only when it adds useful context. Write a concise, imperative description that reflects the staged diff. Keep the type lowercase for consistency. Do not add a trailing period to the subject.

Mark a breaking change with `!` before the colon or with an uppercase `BREAKING CHANGE: <description>` footer. Place a body one blank line after the subject and footers one blank line after the body. Use `-` instead of spaces in footer tokens, except for `BREAKING CHANGE`.

Validate every message before committing:

```bash
python3 .agents/skills/commit-changes/scripts/validate_commit_message.py '<type>(<scope>): <description>'
```

For a multiline message, write it to a temporary file, validate with `--file`, then use the same file with `git commit -F`. The validator checks syntax; independently confirm that `feat`, `fix`, scope, and breaking-change semantics match the staged diff.

### 6. Commit and verify

Create each commit without pushing. Allow repository hooks to run. If a hook fails or changes files, inspect the new state and do not bypass the hook.

After every commit, run:

```bash
git show --stat --oneline --decorate --no-renames HEAD
git status --short
```

Confirm that the commit contains only the intended group. Continue until all owned changes are committed or a safety boundary blocks progress.

### 7. Push only when explicitly requested

When the user explicitly asks to push, inspect the current branch, configured remotes, and upstream first. Push the commits only after all commit verification succeeds. Use the existing upstream when unambiguous; ask before choosing among ambiguous remotes or destinations.

Never force-push unless the user explicitly requests that exact action and the target is verified.

## Report the result

Return:

- Each new commit hash and subject.
- Verification or hook failures that remain.
- A concise note about uncommitted or unowned changes left in the worktree.
- Whether anything was pushed and the destination, or state that no push was performed.
