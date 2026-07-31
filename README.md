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

### Programmes and course units

Admins manage the list of programmes (e.g. "Computer Science" at the
**National Diploma** or **Higher National Diploma** level) that students can
be enrolled into, and the individual course units that make up each
programme's curriculum (e.g. "COM 111 — Introduction to Computing"). This
data is managed first so that, when the student module is built, a
student's programme and scores can be picked from these admin-managed
records instead of being freely typed.

- `/admin/programme` — list, search, add, edit, and delete programmes.
- `/admin/course-unit` — list, add, edit, and delete course units,
  including their credit units, contact hours, and prerequisites.

### Planned next

- Student records (name, year of admission, programme, matric number,
  suspend/reactivate — no hard delete).
- Semester score entry and CGPA calculation.
- Statement of result generation/printing after the 4th semester.

## Tech stack

- Laravel 13 (PHP 8.5)
- Laravel Fortify (authentication: login, registration, 2FA, password reset)
- Backpack for Laravel (the `/admin` panel — programmes, course units, and
  future CRUD screens)
- Livewire 4 + Flux UI (account settings only: profile, appearance,
  security — not used for admin CRUD)
- SQLite (default local database)

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
