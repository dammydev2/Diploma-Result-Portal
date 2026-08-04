{{-- This file is used for menu items by any Backpack v7 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Programmes" icon="la la-graduation-cap" :link="backpack_url('programme')" />
<x-backpack::menu-item title="Add Students" icon="la la-user-plus" :link="backpack_url('student')" />
<x-backpack::menu-item title="Add Course units" icon="la la-book" :link="backpack_url('course-unit')" />
<x-backpack::menu-item title="Grading scale" icon="la la-percent" :link="backpack_url('grading-scale')" />
<x-backpack::menu-item title="Diploma classification" icon="la la-award" :link="backpack_url('diploma-classification')" />
<x-backpack::menu-item title="Score entry" icon="la la-pencil-alt" :link="backpack_url('scores')" />
<x-backpack::menu-item title="Students" icon="la la-users" :link="backpack_url('students')" />
<x-backpack::menu-item title="Courses" icon="la la-list" :link="backpack_url('courses')" />
<x-backpack::menu-item title="Settings" icon="la la-cog" :link="route('page.settings.profile')" />
