# Diploma Result Portal

A web application for a Nigerian diploma-awarding school to manage student
records, record course scores, calculate grade points, and generate
statements of result and transcripts.

## Domain overview

- A diploma programme runs across **4 semesters**.
- At the end of each semester, the student's **CGPA** (Cumulative Grade
  Point Average) is calculated from that semester's scores.
- After the 4th (final) semester, a printable **statement of result** (or
  its raw-score counterpart, the **transcript**) can be generated for the
  student, summarizing performance across all 4 semesters.
- Students are never deleted from the system — they can only be
  **suspended**. A suspended student cannot have new scores recorded against
  them.
- Admins are either a **super admin** (unrestricted) or a regular admin
  limited to an individually-checked set of permissions — see **Roles and
  permissions** below.

## Features

### Public homepage (`/`)

A plain marketing-style landing page for whoever runs the school's records
office — not a student-facing portal, since there is no self-service result
checking in this app (see **Roles and permissions** below). It shows the
institution's name/address (pulled from the same `Setting`-backed values as
the rest of the app, see **Settings**), live counts of programmes and
students, the current programme list, an overview of what the result
management system does, and a **Staff Login** call to action pointing at
`/login`. Built from `WelcomeController` + `resources/views/welcome.blade.php`
rather than the framework's default static `Route::view('/', 'welcome')`, so
it can pull that live data instead of hardcoding it.

The hero background (`public/images/welcome/hero-campus.jpg`) is a bundled
stock photo (Wikimedia Commons, CC BY-SA 3.0) chosen because it shows a
generic campus building with no identifiable people and no other
institution's name legible in the shot — replace it with the school's own
photography whenever available.

### One unified admin panel

Everything in this app — data management (Programmes, Course units,
Students), the course and student browsers, and settings (Profile,
Security, Institution) — lives inside a single Backpack admin panel at
`/admin`, using Backpack's own Bootstrap/Tabler theme throughout.

This wasn't the starting point: the Laravel Livewire starter kit's own
Tailwind/Flux UI shell was originally used for account settings (profile,
security, appearance), which meant switching between two differently-styled
"panels" while signed in. That shell has been removed for authenticated
pages — settings now have Backpack-native (Bootstrap) equivalents reachable
from the account dropdown (click your name, top of the sidebar) or the
**Settings** sidebar link. The Tailwind/Flux UI stack is still used, but only
for the pages a user sees *before* they're authenticated (login, register,
password reset, 2FA challenge), since those can't use the Backpack shell.

### Dashboard (`/admin/dashboard`)

A plain landing page: "Welcome, {name}" and up to three clickable stat
cards — **Total students**, **Total programmes**, **Total admins** — each
linking to its respective section. Backpack's default "Getting Started"
onboarding widget has been removed entirely.

- Each card only appears if the logged-in admin can actually see that
  section: Total students needs `manage_students`, Total programmes needs
  `manage_programmes`, Total admins is super-admin only — same permissions
  that gate the sidebar links (see **Roles and permissions** below), so a
  card never points somewhere the admin would immediately get a 403.
- This overrides Backpack's own `backpack.ui::dashboard` view, at
  `resources/views/vendor/backpack/ui/dashboard.blade.php`.

### Programmes and course units

Admins manage the list of programmes (e.g. "Computer Science" at the
**National Diploma** or **Higher National Diploma** level) that students can
be enrolled into, and the individual course units that make up each
programme's curriculum (e.g. "COM 111 — Introduction to Computing"). This
data is managed first so that, when the student module is built, a
student's programme and scores can be picked from these admin-managed
records instead of being freely typed.

- `/admin/programme` — list, search, add, edit, and delete programmes.
- `/admin/course-unit` — list, add, edit, and delete course units. Each
  course unit only tracks what's actually used in CGPA calculation: code,
  title, credit units, year (1 or 2), and semester (1 or 2) — lecture/
  practical/contact hours and prerequisites were deliberately left out as
  unnecessary for this system.
  - A course unit's **code only needs to be unique within its own
    programme**, not globally — e.g. both "Computer Science" and "Computer
    Engineering" can each have their own "COM 111", since they're different
    curricula that happen to reuse a common numbering scheme.
  - You can't add a course unit until at least one programme exists; the
    Create form redirects back with an explanatory error instead.

### Course browser (`/admin/courses`)

A filtering view for browsing the curriculum: pick a **Programme**, then a
**Year** (labelled dynamically as ND1/ND2 or HND1/HND2 depending on the
programme's level), then a **Semester**, and see that combination's course
units in a table with a bolded **TOTAL UNITS** row summing the credit units.
The filtered result can be exported to **PDF** or **Excel** via buttons
above the table.

### Students

Admins manage student records: name, matric number, year of admission, and
programme.

- `/admin/student` — list, add, edit students. There is deliberately **no
  delete** — per the domain rule above, a student can only be **suspended**
  or **reactivated**, via a line button on the list (this is what a
  suspended student's inability to have new scores recorded will eventually
  hook into). Like course units, you can't add a student until at least one
  programme exists.
- A student's **level** (ND/HND) isn't its own field — it's read from
  `student.programme.level`. Since "Computer Science (ND)" and "Computer
  Science (HND)" are stored as two separate `Programme` rows (see above),
  a student's `programme_id` always unambiguously implies its level, so
  storing level again on the student would just be duplicating that fact.
- A student's **matric number** is unique across the whole school (not
  scoped to a programme, unlike course unit codes).
- A programme can't be deleted while it still has students attached
  (enforced at the database level), consistent with never losing student
  records.

### Student browser (`/admin/students`)

Mirrors the course browser: pick a **Programme**, then a **Year of
admission** (populated from the years that actually have students in that
programme, so you never land on an empty combination), and see matching
students with an Active/Suspended badge and a **TOTAL STUDENTS** row. Also
exportable to **PDF** or **Excel**.

### Grading scale (`/admin/grading-scale`)

The mark-range → letter-grade → weighting table used to convert a raw score
into a grade point when scores are recorded:

| Marked range  | Letter grade | Weighting |
|---------------|:------------:|:---------:|
| 75 and above  | A            | 4.00      |
| 70 – 74       | AB           | 3.50      |
| 65 – 69       | B            | 3.25      |
| 60 – 64       | BC           | 3.00      |
| 55 – 59       | C            | 2.75      |
| 50 – 54       | CD           | 2.50      |
| 45 – 49       | D            | 2.25      |
| 40 – 44       | E            | 2.00      |
| Below 40      | F            | 0.00      |

This is fixed reference data, seeded via `GradingScaleSeeder`, so the admin
screen deliberately only supports **viewing and editing** existing bands,
not adding or deleting them. `GradingScale::forScore(int $score)` looks up
the matching band for a given mark and will be what result entry uses to
derive a grade.

### Diploma classification (`/admin/diploma-classification`)

The CGPA → class-of-pass table used once a student's final CGPA is
calculated, to determine their overall result classification:

| Class (level of pass) | CGPA           |
|------------------------|:--------------:|
| Distinction             | 3.50 and above |
| Upper Credit             | 3.00 – 3.49    |
| Lower Credit             | 2.50 – 2.99    |
| Pass                     | 2.00 – 2.49    |
| Fail                     | Below 2.00     |

Same shape as the grading scale above: fixed reference data seeded via
`DiplomaClassificationSeeder`, admin screen only supports **viewing and
editing**, and `DiplomaClassification::forCgpa(float $cgpa)` looks up the
matching band for a given CGPA.

### Score entry (`/admin/scores`)

A bulk "mark sheet" for recording scores: pick a **Programme**, then a
**Year of admission** (the student cohort — same dimensions as the student
browser above), then a **Course unit**, and enter a 0–100 score for each
student in that cohort in one form, submitted all at once. Suspended
students show with no input at all, so they can never get a new score
regardless of what's submitted (enforced again server-side, not just hidden
in the UI). Existing scores pre-fill so the sheet doubles as an edit screen.

The letter grade and grade point are **derived and stored at the point of
entry** (not recalculated later) — `Score` automatically looks up the
matching `GradingScale` band whenever its `score` is set or changed. This
was a deliberate choice: a later edit to the grading scale table shouldn't
silently rewrite a result that's already been recorded.

GPA calculation follows the standard formula, using each course unit's
credit units as the weight:

```
GPA = Σ(grade_point × credit_units) / Σ(credit_units)
```

- `Student::gpaFor(int $year, int $semester)` — GPA for one semester of study.
- `Student::cgpa()` — cumulative GPA across every score recorded so far.

### Semester result (`/admin/results`)

A full result sheet for a programme's cohort: pick a **Programme**, then a
**Year of admission**, **Year**, and **Semester**, and see every student in
that cohort against every course unit for that year/semester, matrix-style
— matric number down the side, course codes across the top, each cell
showing the recorded score with its weighting and letter grade underneath
it, and a **GP** column per student. Exportable to **PDF** (landscape) or
**Excel**, both following the same layout as the on-screen view, headed by
the institution name/address — editable at `/admin/settings/institution`
(see **Settings** below) — followed by the programme and level/year (e.g.
"COMPUTER SCIENCE ND 1"), then the session/semester title.

- The result for a given programme/cohort/year/semester combination is only
  viewable or downloadable once **at least one student** has a complete set
  of scores (one for every course unit in that year/semester) — otherwise
  the page shows a "not ready yet" message instead of a half-finished
  result, and the export routes 404.
- Once ready, **all** students in the cohort are still shown, including
  incomplete ones — a student missing a score for a course just gets a
  blank cell for it, rather than being hidden or shown a zero.
- Every result carries a footnote: a blank score, or a score below 40
  (grade F), is an automatic carry-over and the student must re-sit the
  course next session.
- For any semester **after** the programme's very first one (i.e. not Year
  1 Semester 1), two extra columns appear after **GP**: **GPA** (the
  student's cumulative GPA through the *previous* semester — their standing
  coming into this one) and **CGPA** (cumulative GPA through *this*
  semester inclusive — their standing as of this result). Both use the same
  credit-unit-weighted formula as `Student::cgpa()`, just bounded to a
  point in time via `Student::cgpaThroughSemester(int $year, int $semester)`,
  rather than a simple average of each semester's GP — so a semester with a
  heavier course load counts more, consistent with the rest of the app.
- While not ready, the page also names any course unit that has **no score
  recorded for any student yet** (e.g. "No scores have been entered yet
  for: COM 112, COM 113") — a hint at why the result isn't ready, since a
  totally unscored course makes the "at least one complete student" bar
  impossible to clear. This can't happen once a result is ready (a complete
  student's row necessarily covers every course), so the note only ever
  shows on the not-ready screen, never on the printed PDF/Excel.

### Statement of result (`/admin/statement-of-result`)

A single student's full academic transcript, covering every semester of
their programme on one printable page: pick a **Programme**, then a **Year
of admission**, then a **Student**, and (once their result is complete) see
their statement — student info block, then one bordered box per (year,
semester) block laid out 2-per-row (matching the classic two-session-per-
page transcript layout), each showing that semester's courses with score,
credit units, and letter grade, plus **MAX UNIT**/**UNIT PASSED** and
**GPA**/**CGPA** for that point in the programme. A **Date of approval**
field (defaulting to today, editable) feeds both the document's date field
and its "date of approval by the academic board" line. Exportable to
**PDF**, matching the on-screen preview.

- Only viewable/downloadable once the student has a recorded score for
  **every** course unit in their programme (`Student::hasCompleteResult()`)
  — otherwise the page explains the result isn't complete yet, and the
  export route 404s. Unlike Semester Result, there's no "blank cell"
  allowance here — a statement of result is only generated once a
  student's full record exists.
- Each semester block's **GPA** and **CGPA** use the same
  `Student::gpaFor()` / `Student::cgpaThroughSemester()` methods as the
  Semester Result page, so the numbers agree everywhere in the app.
  **UNIT PASSED** only counts credit units for courses graded above F (a
  passed course still counts toward MAX UNIT either way).
- The final **CUMMULATIVE GPA** and **FINAL GRADE/CLASS** (via
  `DiplomaClassification::forCgpa()`) summarize the whole programme.
- The page and PDF are built from a shared Blade partial
  (`admin.statement-of-result-document`) so the on-screen preview and the
  printed PDF never drift apart.
- Every semester box on the page renders at the same height, regardless of
  how many courses it holds — the shorter boxes are padded with blank
  table rows so the grid lines up cleanly. Since there's no way to know a
  course title's actual wrapped line count ahead of PDF rendering,
  `StatementOfResultController::estimateBlockLines()` approximates it from
  title length and deliberately over-estimates — a little extra blank
  space at the bottom of a box beats boxes that visibly don't line up.
- The background artwork, and the registrar's name/title/signature, are
  configured on the **Institution** settings page (see below) — this is
  the "branding" of the document the school actually issues, not something
  this app invents. The bundled defaults ship pre-filled to match exactly
  what the school already issues, extracted from a real sample statement
  of result provided when this feature was built.

### Transcript (`/admin/transcript`)

The same document as the Statement of Result above — same filters, same
per-(year, semester) box layout, same GPA/CGPA/classification figures,
same blank-row padding, same shared-partial/PDF-parity approach — with one
difference: each course row shows the student's **raw score** (0–100)
instead of its derived letter grade. Everything else, including the
"complete result required" gate and `super_admin_only` restriction, is
identical.

- Built from `TranscriptController`, a near line-for-line copy of
  `StatementOfResultController` — the GPA/CGPA/classification math is
  untouched (it's driven by `Score::weighting`, not by which column is
  displayed), so only the document partial's course-row markup differs
  (`$row['score']?->score` instead of `$row['score']?->letter_grade`,
  header "SCORE" instead of "GRADE").
- Uses its own shared partial (`admin.transcript-document`) and its own
  prefixed CSS (`.tr-*` in `admin.transcript-styles`) so it can evolve
  independently of the Statement of Result's markup/styling.
- Has its **own background artwork**, configurable separately from the
  Statement of Result's — see **Settings** below — so a school can use
  different letterhead art for each document. The registrar's
  name/title/signature are shared between both, since both are signed off
  by the same registrar.

### Settings (`/admin/settings`)

Account settings (Profile, Security — password, 2FA, passkeys) live here,
alongside **Institution** (`/admin/settings/institution`): everything
shown on printable documents — institution name/address (semester result,
statement of result, and transcript), the Statement of Result's background
artwork, the Transcript's background artwork (uploaded separately, so each
document can carry different letterhead art), and the shared registrar
name/title/signature (uploaded as images). All of this is admin-editable
rather than `.env`-only, so a non-technical admin can update it without a
deploy, and so the same mechanism scales to other institutions/deployments
later.

It's backed by a generic key-value `settings` table (`Setting` model,
`Setting::get(string $key, ?string $default = null)` /
`Setting::set(string $key, ?string $value)`), rather than a one-off table
just for institution details — any future admin-editable setting reuses
this instead of a new migration. Uploaded images are stored via the
`public` filesystem disk (`storage/app/public`, symlinked at
`public/storage`) under `institution/`, replacing (and deleting) whatever
was previously uploaded for that setting (`statement_background_path`,
`transcript_background_path`, `registrar_signature_path`).
`config/institution.php` holds the factory-default values and bundled
default images (`public/images/statement-of-result/` and
`public/images/transcript/`), used as the fallback until an admin
uploads/saves their own — the transcript's bundled default currently
reuses the statement of result's artwork as a starting point until a
school uploads dedicated transcript letterhead art.

### Roles and permissions (`/admin/admins`)

Every admin account is either a **super admin** (unrestricted access to
everything) or a regular admin with an individually-checked set of
permissions. There's no named "role" in between — the checkboxes granted to
an admin *are* their role.

- `/admin/admins`, **super admin only**: add other admins, and for each one
  either make them a super admin or check exactly which of the following
  they can do — **Programmes**, **Students**, **Suspend and reactivate a
  student**, **Add course unit**, **Edit course unit**, **Edit grading
  scale**, **Edit diploma classification**, **Score entry**, **Edit
  score**, **Semester result**. Three areas are deliberately *not*
  checkboxes and can only ever be done by a super admin: **Statement of
  Result**, **Transcript**, and **Institution settings**.
- **Add course unit** vs **Edit course unit**, and **Score entry** vs
  **Edit score**, are intentionally separate — an admin can be allowed to
  enter brand new records without being allowed to change ones that
  already exist, or vice versa. `CourseUnitCrudController::setup()` denies
  Backpack's `create`/`update`/`delete` operations individually based on
  which of the two the current user has. `ScoresController::store()` does
  the equivalent per submitted score, checking whether a `Score` already
  exists for that student/course before deciding whether the *entry* or
  the *edit* permission applies to that one cell — silently skipping it
  otherwise, the same way it already silently skips a suspended student's
  score. **Suspend and reactivate a student** is likewise its own
  permission, separate from general **Students** access.
- Enforcement lives at the route level (`routes/backpack/custom.php`),
  where every route is wrapped in `Route::middleware('can:...')`, checked
  against Laravel Gates registered per `App\Enums\Permission` case (plus a
  few compound gates — e.g. `view_course_units` passes for *either*
  **Add** or **Edit course unit** — see
  `AppServiceProvider::configurePermissionGates()`). The sidebar
  (`menu_items.blade.php`) wraps each item in a matching `@can(...)`, so an
  admin only ever sees the pages they can actually open.
- `User::hasPermission(Permission $permission)` is the single source of
  truth — always `true` for a super admin regardless of their individual
  `permissions`, otherwise checks that JSON-backed column (cast to a
  `Collection<Permission>` via `AsEnumCollection`).
- Self-lockout guards on `AdminUserController`: you can't delete your own
  account, and the last remaining super admin can't demote themselves —
  both would otherwise be able to lock every admin out of the panel.
- Every account that existed before this feature shipped was promoted to
  super admin in the migration that added it, so nobody already using the
  app lost access. Only admins created afterward start out
  permission-limited by default.

## Tech stack

- Laravel 13 (PHP 8.5)
- Laravel Fortify (authentication: login, registration, 2FA with recovery
  codes, passkeys, password reset)
- Backpack for Laravel (the `/admin` panel — all data management, the
  course/student browsers, and account settings share this one theme)
- Livewire 4 + Flux UI (guest-facing auth pages only — login, register,
  password reset, 2FA challenge; not used anywhere behind login)
- barryvdh/laravel-dompdf (PDF export on the course/student browsers, the
  semester result, and the statement of result)
- openspout/openspout (Excel export on the course/student browsers and the
  semester result — `maatwebsite/excel` was tried first but its underlying
  spreadsheet library doesn't yet support PHP 8.5)
- MySQL (local development database, via `.env`)

## Local development

```bash
composer run dev
```

This runs the PHP server, queue listener, log tailer, and Vite dev server
together. The app will be available at `http://localhost:8000`.

## Testing

```bash
php artisan test --compact
```

Tests always run against an in-memory SQLite database — `phpunit.xml`
overrides `DB_CONNECTION`/`DB_DATABASE` for the test environment, so running
the suite never touches the MySQL database configured in `.env`.
