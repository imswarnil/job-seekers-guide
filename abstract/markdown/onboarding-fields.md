# Onboarding Fields

The exact field and option lists for the wizard. Stored as user meta under the
`jsl_onboarding_*` prefix. Screens: [../flows/](../flows/).

## Step 1 — About you

| Meta key | Type | Required | Options |
|---|---|---|---|
| `jsl_full_name` | string | ✅ | — |
| `jsl_photo_id` | attachment ID | — | — |
| `jsl_dob` | date | ✅ | — |
| `jsl_city` | string | ✅ | autocomplete + free text |

## Step 2 — Your education

| Meta key | Type | Required |
|---|---|---|
| `jsl_college` | string | — |
| `jsl_degree` | enum | ✅ |
| `jsl_branch` | enum | ✅ |
| `jsl_grad_year` | int | ✅ |
| `jsl_current_status` | enum | ✅ |

**`jsl_degree`** — `be-btech` · `bsc` · `bca` · `bcom` · `ba` · `mca` · `mtech` ·
`msc` · `mba` · `diploma` · `medical` · `other`

**`jsl_branch`** — `cse` · `it` · `ece` · `eee` · `mechanical` · `civil` ·
`chemical` · `biotech` · `physics` · `chemistry` · `maths` · `commerce` ·
`arts` · `medical` · `law` · `other`

Derived: `jsl_branch_group` = `cs` if branch ∈ {cse, it} else `non-cs`.

**`jsl_current_status`** — `studying` · `graduated-searching` ·
`working-non-it` · `working-it`

## Step 3 — Where are you now

| Meta key | Type | Required |
|---|---|---|
| `jsl_level` | int 1–4 | ✅ |

| Value | Label | Effect |
|---|---|---|
| 1 | Start me from the very beginning | Full `CS-000`, full foundations, gentlest pace |
| 2 | I know a little | Condensed `CS-000`, application-first foundations |
| 3 | I can code, I can't get hired | Foundations as revision, System Design early, full career module |
| 4 | I'm working, I want a better job | Depth + System Design + expanded `JOB-201`, part-time pace |

## Step 4 — Where do you want to reach

| Meta key | Type | Required |
|---|---|---|
| `jsl_goal` | enum | ✅ |
| `jsl_target_role` | enum | — (defaults `help-me-decide`) |

**`jsl_goal`** — `first-job` · `switch-into-it` · `better-job` · `dont-know`

**`jsl_target_role`** — `backend-developer` · `frontend-developer` ·
`full-stack` · `data-engineer` · `data-analyst` · `qa-engineer` ·
`devops-engineer` · `mobile-developer` · `salesforce-developer` ·
`business-analyst` · `support-engineer` · `help-me-decide`

## Step 5 — Foundation subjects

| Meta key | Type | Required |
|---|---|---|
| `jsl_subjects` | array of course codes | ✅ (min 1) |
| `jsl_pace` | enum | ✅ |

Selectable: `CS-OS-101` `CS-NET-101` `CS-DB-101` `CS-OOP-101` `CS-DSA-101`
`CS-SE-101` `CS-WEB-101` `CS-SYS-101` `CS-AI-101` `CS-TOC-101` `SOFT-101`

**Always included, not selectable:** `CS-000`

Presets:
- **Select all (recommended)** — all 11
- **Just the essentials** — `CS-OS-101` `CS-NET-101` `CS-DB-101` `CS-OOP-101`
  `CS-DSA-101` `CS-WEB-101`

**`jsl_pace`** — `relaxed` (6–8 h/wk) · `regular` (12–15) · `intense` (25+)

## Step 6 — Language

| Meta key | Type | Required |
|---|---|---|
| `jsl_language` | enum | ✅ |

`java` *(recommended default)* · `python` · `typescript` · `cpp`

## Step 7 — Track

| Meta key | Type | Required |
|---|---|---|
| `jsl_tracks` | array, max 2 | ✅ (defaults `web`) |

`web` *(default)* · `data` · `devops` · `qa` · `mobile` · `salesforce` · `ai`

## Step 8 — Assembled

| Meta key | Type |
|---|---|
| `jsl_assigned_path` | path slug |
| `jsl_path_customised` | bool — true once the learner edits the assembled path |
| `jsl_onboarding_step` | int 1–8, `0` = complete |
| `jsl_onboarding_completed_at` | datetime |

## Rules

1. **`jsl_onboarding_step` is written after every step** so an abandoned wizard
   resumes exactly where it stopped.
2. **Every field is editable later** from Settings → My Path, which recomposes
   the path and shows a diff before confirming.
3. **Completed progress is never destroyed** by a change of answers.
4. **No CGPA, no marks, no gender, no salary-history field.** Not collected, at
   any step, ever.
5. `jsl_dob` and `jsl_city` are private by default; only `jsl_full_name` and
   `jsl_photo_id` appear on public surfaces (Wall of Success, leaderboard), and
   only with opt-in.
