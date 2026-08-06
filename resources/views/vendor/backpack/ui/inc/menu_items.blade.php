{{-- This file is used for menu items by any Backpack v7 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

@can('manage_programmes')
    <x-backpack::menu-item title="Programmes" icon="la la-graduation-cap" :link="backpack_url('programme')" />
@endcan
@can('manage_students')
    <x-backpack::menu-item title="Add Students" icon="la la-user-plus" :link="backpack_url('student')" />
@endcan
@can('view_course_units')
    <x-backpack::menu-item title="Add Course units" icon="la la-book" :link="backpack_url('course-unit')" />
@endcan
@can('edit_grading_scale')
    <x-backpack::menu-item title="Grading scale" icon="la la-percent" :link="backpack_url('grading-scale')" />
@endcan
@can('edit_diploma_classification')
    <x-backpack::menu-item title="Diploma classification" icon="la la-award" :link="backpack_url('diploma-classification')" />
@endcan
@can('view_scores')
    <x-backpack::menu-item title="Score entry" icon="la la-pencil-alt" :link="backpack_url('scores')" />
@endcan
@can('semester_result')
    <x-backpack::menu-item title="Semester result" icon="la la-file-alt" :link="backpack_url('results')" />
@endcan
@can('super_admin_only')
    <x-backpack::menu-item title="Statement of result" icon="la la-scroll" :link="backpack_url('statement-of-result')" />
@endcan
@can('super_admin_only')
    <x-backpack::menu-item title="Transcript" icon="la la-file-invoice" :link="backpack_url('transcript')" />
@endcan
@can('manage_students')
    <x-backpack::menu-item title="Students" icon="la la-users" :link="backpack_url('students')" />
@endcan
@can('view_course_units')
    <x-backpack::menu-item title="Courses" icon="la la-list" :link="backpack_url('courses')" />
@endcan
@can('super_admin_only')
    <x-backpack::menu-item title="Admins" icon="la la-user-shield" :link="route('page.admins.index')" />
@endcan
<x-backpack::menu-item title="Settings" icon="la la-cog" :link="route('page.settings.profile')" />
