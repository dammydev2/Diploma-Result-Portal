{{-- This file is used for menu items by any Backpack v7 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Programmes" icon="la la-graduation-cap" :link="backpack_url('programme')" />
<x-backpack::menu-item title="Course units" icon="la la-book" :link="backpack_url('course-unit')" />