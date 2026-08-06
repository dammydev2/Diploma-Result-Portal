<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="sticky top-0 z-40 border-b border-zinc-200/80 bg-white/90 backdrop-blur dark:border-zinc-800/80 dark:bg-zinc-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3" wire:navigate>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-900 text-white dark:bg-blue-800">
                        <flux:icon.academic-cap variant="solid" class="size-6" />
                    </span>
                    <span class="flex flex-col leading-tight">
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $institutionName }}</span>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Result Management Portal</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-medium text-zinc-600 md:flex dark:text-zinc-300">
                    <a href="#about" class="hover:text-zinc-900 dark:hover:text-white">About</a>
                    <a href="#programmes" class="hover:text-zinc-900 dark:hover:text-white">Programmes</a>
                    <a href="#features" class="hover:text-zinc-900 dark:hover:text-white">Result management</a>
                    <a href="#contact" class="hover:text-zinc-900 dark:hover:text-white">Contact</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600"
                            wire:navigate
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="rounded-md bg-blue-900 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600"
                            wire:navigate
                        >
                            Staff Login
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero. Background photo: "College of Human Sciences Building at Texas
             Tech", Wikimedia Commons, CC BY-SA 3.0 (public/images/welcome/hero-campus.jpg) --}}
        <section class="relative isolate overflow-hidden">
            <img
                src="{{ asset('images/welcome/hero-campus.jpg') }}"
                alt="A university campus building"
                class="absolute inset-0 h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/30"></div>

            <div class="relative mx-auto flex min-h-[34rem] max-w-7xl flex-col justify-end px-6 py-20 sm:min-h-[38rem]">
                <p class="mb-4 inline-flex w-fit items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold tracking-wider text-amber-300 uppercase ring-1 ring-white/20 ring-inset">
                    National &amp; Higher National Diploma
                </p>
                <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ $institutionName }}
                </h1>
                <p class="mt-4 max-w-2xl text-lg text-zinc-200">
                    The official result management portal — from score entry and CGPA
                    calculation to printable semester results, statements of result,
                    and transcripts.
                </p>
                @if ($institutionAddress)
                    <p class="mt-3 flex items-center gap-2 text-sm text-zinc-300">
                        <flux:icon.map-pin class="size-4 shrink-0" />
                        {{ $institutionAddress }}
                    </p>
                @endif
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-6 py-3 text-sm font-semibold text-zinc-900 hover:bg-amber-400"
                        wire:navigate
                    >
                        Staff Login
                        <flux:icon.arrow-right class="size-4" />
                    </a>
                    <a href="#about" class="inline-flex items-center gap-2 rounded-md border border-white/30 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                        Learn more
                    </a>
                </div>
            </div>
        </section>

        {{-- Stats strip --}}
        <section class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-6 py-10 sm:grid-cols-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-400">{{ $totalProgrammes }}</div>
                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Programmes offered</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-400">{{ $totalStudents }}</div>
                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Students on record</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-400">2</div>
                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Diploma levels — ND &amp; HND</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-400">4</div>
                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Semesters per programme</div>
                </div>
            </div>
        </section>

        {{-- About --}}
        <section id="about" class="mx-auto max-w-7xl px-6 py-20">
            <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h2 class="text-sm font-semibold tracking-wider text-blue-900 uppercase dark:text-blue-400">About the portal</h2>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">
                        Every result, calculated the same way, every time.
                    </p>
                    <p class="mt-4 text-base text-zinc-600 dark:text-zinc-400">
                        {{ $institutionName }} runs its National Diploma and Higher
                        National Diploma programmes on a single result management
                        system used by the records office from the moment a score is
                        entered to the moment a student's statement of result or
                        transcript is printed. Grade points, GPA, and CGPA are
                        calculated with one consistent formula across every
                        programme, every semester, and every cohort.
                    </p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800">
                        <flux:icon.document-check class="size-6 text-blue-900 dark:text-blue-400" />
                        <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Accurate by design</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Grades, GPA, and CGPA are derived automatically from the grading scale, never re-typed.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800">
                        <flux:icon.shield-check class="size-6 text-blue-900 dark:text-blue-400" />
                        <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Role-based access</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Staff only see and do what their permissions allow — from score entry to final sign-off.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800">
                        <flux:icon.clipboard-document-check class="size-6 text-blue-900 dark:text-blue-400" />
                        <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Print-ready documents</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Semester results, statements of result, and transcripts export straight to PDF.</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-800">
                        <flux:icon.user-group class="size-6 text-blue-900 dark:text-blue-400" />
                        <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">A record that lasts</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Students are never deleted — only suspended and reactivated — so history is never lost.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Programmes --}}
        <section id="programmes" class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/40">
            <div class="mx-auto max-w-7xl px-6 py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-sm font-semibold tracking-wider text-blue-900 uppercase dark:text-blue-400">Programmes</h2>
                    <p class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">What we offer</p>
                </div>

                @if ($programmes->isEmpty())
                    <p class="mx-auto mt-10 max-w-md text-center text-sm text-zinc-500 dark:text-zinc-400">
                        Programmes will appear here once they've been added by the
                        records office.
                    </p>
                @else
                    <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($programmes as $programme)
                            <div class="flex items-center gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-950">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-blue-900/10 text-blue-900 dark:bg-blue-400/10 dark:text-blue-400">
                                    <flux:icon.building-library class="size-5" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-zinc-900 dark:text-white">{{ $programme->name }}</p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $programme->level->label() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Result management features --}}
        <section id="features" class="mx-auto max-w-7xl px-6 py-20">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-sm font-semibold tracking-wider text-blue-900 uppercase dark:text-blue-400">Result management</h2>
                <p class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">One system, start to finish</p>
            </div>

            <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <flux:icon.bolt class="size-6 text-amber-500" />
                    <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Score entry &amp; grading</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Bulk score entry per course unit, with letter grades and grade points derived automatically from the grading scale.</p>
                </div>
                <div>
                    <flux:icon.chart-bar class="size-6 text-amber-500" />
                    <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">GPA &amp; CGPA engine</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Credit-unit-weighted GPA and CGPA, calculated the same way on every screen and every printed document.</p>
                </div>
                <div>
                    <flux:icon.document-text class="size-6 text-amber-500" />
                    <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Semester result sheets</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Full cohort result matrices, exportable to PDF and Excel, headed by the institution's own letterhead.</p>
                </div>
                <div>
                    <flux:icon.check-badge class="size-6 text-amber-500" />
                    <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Statement of result</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">A student's full academic record across every semester, with final CGPA and classification.</p>
                </div>
                <div>
                    <flux:icon.clipboard-document-check class="size-6 text-amber-500" />
                    <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Transcript</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">The same academic record, showing raw scores rather than letter grades, on its own letterhead artwork.</p>
                </div>
                <div>
                    <flux:icon.shield-check class="size-6 text-amber-500" />
                    <h3 class="mt-3 font-semibold text-zinc-900 dark:text-white">Roles &amp; permissions</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Every member of staff is limited to exactly the actions they've been granted — nothing more.</p>
                </div>
            </div>
        </section>

        <footer id="contact" class="border-t border-zinc-200 bg-zinc-900 dark:border-zinc-800">
            <div class="mx-auto max-w-7xl px-6 py-12">
                <div class="flex flex-col items-start justify-between gap-8 sm:flex-row">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-blue-800 text-white">
                                <flux:icon.academic-cap variant="solid" class="size-5" />
                            </span>
                            <span class="font-semibold text-white">{{ $institutionName }}</span>
                        </div>
                        @if ($institutionAddress)
                            <p class="mt-3 flex items-center gap-2 text-sm text-zinc-400">
                                <flux:icon.map-pin class="size-4 shrink-0" />
                                {{ $institutionAddress }}
                            </p>
                        @endif
                    </div>

                    <div class="text-sm text-zinc-400">
                        <a href="{{ route('login') }}" class="font-medium text-white hover:underline" wire:navigate>Staff Login →</a>
                    </div>
                </div>

                <div class="mt-10 border-t border-zinc-800 pt-6 text-sm text-zinc-500">
                    &copy; {{ now()->year }} {{ $institutionName }}. All rights reserved.
                </div>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
