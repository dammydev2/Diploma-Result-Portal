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

Everything in this app — data management (Programmes, Course units), the
course browser, and account settings (Profile, Security) — lives inside a
single Backpack admin panel at `/admin`, using Backpack's own Bootstrap/Tabler
theme throughout.

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

### Planned next

- Student records (name, year of admission, programme, matric number,
  suspend/reactivate — no hard delete).
- Semester score entry and CGPA calculation.
- Statement of result generation/printing after the 4th semester.

## Tech stack

- Laravel 13 (PHP 8.5)
- Laravel Fortify (authentication: login, registration, 2FA with recovery
  codes, passkeys, password reset)
- Backpack for Laravel (the `/admin` panel — all data management, the course
  browser, and account settings share this one theme)
- Livewire 4 + Flux UI (guest-facing auth pages only — login, register,
  password reset, 2FA challenge; not used anywhere behind login)
- barryvdh/laravel-dompdf (PDF export on the course browser)
- openspout/openspout (Excel export on the course browser — `maatwebsite/
  excel` was tried first but its underlying spreadsheet library doesn't yet
  support PHP 8.5)
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
