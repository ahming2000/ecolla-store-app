---
name: update-changing-log
description: Inspect Git commits since the last processed checkpoint, select only user-visible changes, choose the next Semantic Versioning bump, and update this project's English and Chinese changing-log JSON files. Use when Codex is asked to generate release notes, update the changing log or changelog, prepare a new version entry, or decide the next application version from recent commits.
---

# Update Changing Log

Generate concise bilingual release notes from committed changes while preserving the project's existing JSON structure and release history.

## Files

- English: `docs/changing-logs.en.json`
- Simplified Chinese: `docs/changing-logs.zh.json`
- Git checkpoint: `docs/changing-logs.meta.json`
- Read-only inspector: `scripts/inspect_changes.py`

## Workflow

1. Run the inspector from the repository root:

   ```bash
   python3 .agents/skills/update-changing-log/scripts/inspect_changes.py
   ```

2. Treat its `baselineCommit..headCommit` range as the candidate release range. Review every returned commit with `git show --stat --patch <hash>` as needed. Use the diff and resulting behavior as the authority; commit subjects are only hints.
3. Inspect any existing changes to the two changing-log files before editing. Do not overwrite or duplicate an uncommitted release entry. Exclude all other uncommitted work unless the user explicitly includes it.
4. Keep only changes a customer, staff member, administrator, or visitor can notice:
   - Include new capabilities, changed workflows, visible fixes, meaningful performance improvements, removals, and user-facing compatibility changes.
   - Exclude tests, refactors, renames with unchanged behavior, tooling, CI, agent configuration, comments, formatting, and dependency maintenance with no visible effect.
   - Combine implementation commits that deliver one outcome into one release-note sentence.
5. If no eligible change exists, do not create a release. Advance only `lastProcessedCommit` in `docs/changing-logs.meta.json` to `headCommit`, validate the metadata JSON, and report that no user-visible release note was needed.
6. Choose one version bump using the highest-impact eligible change.
7. Draft English first, then write natural Simplified Chinese with the same meaning. Correct grammar and rewrite developer-centric wording for users.
8. Update both JSON files together, update affected test expectations, verify the rendered changing-log page, and then advance the metadata checkpoint to the inspector's `headCommit`.

## Semantic Versioning

Follow [Semantic Versioning 2.0.0](https://semver.org/) using the current `vX.Y.Z` value:

- **MAJOR** (`X+1.0.0`): a user-facing incompatible change, removed or fundamentally changed supported workflow, or other breaking product behavior.
- **MINOR** (`X.Y+1.0`): any backward-compatible user-visible capability. A minor release may also contain fixes and improvements.
- **PATCH** (`X.Y.Z+1`): only backward-compatible bug fixes, performance improvements, reliability improvements, or small user-facing polish.

When evidence does not establish a breaking change, do not assume one. If the range contains both features and fixes, use a minor bump. If it contains a breaking change, use a major bump.

## Release-note Style

Write outcomes rather than implementation details:

- Prefer “Added password updates from the profile page.” over controller, route, request, or component names.
- Use “Improved performance and responsiveness.” when the implementation is too complex to explain usefully.
- Use “Fixed minor issues to improve reliability.” only as a truthful fallback when individual fixes are too small or technical to describe.
- Do not mention commit hashes, internal class names, test coverage, migrations, packages, or refactors in user-facing descriptions.
- Do not overclaim behavior that the diff does not prove.

Use only non-empty categories in this order:

1. `Features` / `功能` — new user-visible capabilities.
2. `Improvements` / `优化` — fixes, performance, reliability, and usability improvements.
3. `Removed` / `移除` — intentionally removed user-visible behavior.
4. `Other` / `其他` — relevant release information that fits nowhere else.

English descriptions use sentence case and a final period. Chinese descriptions use concise natural wording and follow the existing file's punctuation style.

## JSON Structure

- Set `versionName`, `versionLabel`, and `updateDate` to the new release.
- Use the current Asia/Singapore date in `YYYY/MM/DD`.
- Preserve the current release channel wording: `Public Release` in English and `正式版` in Chinese, unless the user explicitly requests another channel.
- For a patch release with the same `X.Y` as `logs[0]`, prepend a new subgroup to `logs[0].subGroups`.
- For a minor or major release, prepend a new group:
  - English group: `vX.Y Public Release`
  - English subgroup: `vX.Y.Z Public Release`
  - Chinese group: `vX.Y 正式版`
  - Chinese subgroup: `vX.Y.Z 正式版`
- Keep all older groups and subgroups unchanged and in descending version order.
- Preserve four-space JSON indentation and the existing object/array shape.

## Checkpoint Rules

`docs/changing-logs.meta.json` records the latest source commit that was examined, not the later commit that may save the release notes.

- Copy the inspector's full `headCommit` into `lastProcessedCommit` only after the changelog edits are complete.
- Never invent, abbreviate, or manually derive the hash.
- A previous commit that only updated the changing log may appear in the next candidate range. Treat it as internal work and do not publish it as a release note.
- If the recorded commit is unavailable or not an ancestor of `HEAD`, the inspector falls back to the newest reachable commit that changed the bilingual changelog. Review the reported source before continuing.

## Verification

Follow the repository's `AGENTS.md` instructions. At minimum:

```bash
jq empty docs/changing-logs.en.json docs/changing-logs.zh.json docs/changing-logs.meta.json
npx prettier --write docs/changing-logs.en.json docs/changing-logs.zh.json
vendor/bin/pint --dirty --format agent
php artisan test --compact tests/Feature/LanguagePreferenceTest.php
```

Update and run `e2e/admin-changing-log-language.spec.ts` with Playwright because the content is frontend-visible. Search for stale version assertions with:

```bash
rg -n "versionLabel|v[0-9]+\.[0-9]+\.[0-9]+" tests e2e
```

Report the chosen bump, processed commit range, included user-visible changes, ignored internal changes, checkpoint hash, and verification results. Do not commit or push unless the user asks.
