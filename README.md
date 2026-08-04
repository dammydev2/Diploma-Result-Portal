# Diploma Result Portal

A web application for a Nigerian diploma-awarding school to manage student
records, record course scores, calculate grade points, and generate
statements of result.

## Domain overview

- A diploma programme runs across **4 semesters**.
- At the end of each semester, the student's **CGPA** (Cumulative Grade
  Point Average) is calculated from that semester's scores.
- After the 4th (final) semester, a printable **statement of result** can be
  generated for the student, summarizing performance across all 4 semesters.
- Students are never deleted from the system — they can only be
  **suspended**. A suspended student cannot have new scores recorded against
  them.

## Features

### One unified admin panel

Everything in this app — data management (Programmes, Course units,
Students), the course and student browsers, and account settings (Profile,
Security) — lives inside a single Backpack admin panel at `/admin`, using
Backpack's own Bootstrap/Tabler theme throughout.

This wasn't the starting point: the Laravel Livewire starter kit's own
Tailwind/Flux UI shell was originally used for account settings (profile,
security, appearance), which meant switching between two differently-styled
"panels" while signed in. That shell has been removed for authenticated
pages — settings now have Backpack-native (Bootstrap) equivalents reachable
from the account dropdown (click your name, top of the sidebar) or the
**Settings** sidebar link. The Tailwind/Flux UI stack is still used, but only
for the pages a user sees *before* they're authenticated (login, register,
password reset, 2FA challenge), since those can't use the Backpack shell.

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

### Planned next

- Statement of result generation/printing after the 4th semester, combining
  the diploma classification table above with a student's final CGPA.

## Tech stack

- Laravel 13 (PHP 8.5)
- Laravel Fortify (authentication: login, registration, 2FA with recovery
  codes, passkeys, password reset)
- Backpack for Laravel (the `/admin` panel — all data management, the
  course/student browsers, and account settings share this one theme)
- Livewire 4 + Flux UI (guest-facing auth pages only — login, register,
  password reset, 2FA challenge; not used anywhere behind login)
- barryvdh/laravel-dompdf (PDF export on the course and student browsers)
- openspout/openspout (Excel export on the course and student browsers —
  `maatwebsite/excel` was tried first but its underlying spreadsheet library
  doesn't yet support PHP 8.5)
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
