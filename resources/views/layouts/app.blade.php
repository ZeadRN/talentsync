<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>@yield('title','Your next chapter') · TalentSync</title>
 <link href="{{ asset('vendor/bootstrap.min.css') }}" rel="stylesheet">
 <link href="{{ asset('css/app.css') }}?v=2" rel="stylesheet">
 <link href="{{ asset('css/platform.css') }}?v=2" rel="stylesheet">
</head>
<body class="{{ auth()->check()?'workspace':'public-site' }}">
@auth
@php($navView=request('view','overview'))
<aside class="sidebar" id="sidebar">
 <a class="brand" href="/"><span class="brand-symbol">t<span>↗</span></span>TalentSync<span class="brand-period">.</span></a>
 <div class="workspace-label"><span class="workspace-dot"></span>{{ auth()->user()->can('manage platform')?'Platform workspace':(auth()->user()->can('create jobs')?'Employer workspace':'Candidate workspace') }}</div>
 <span class="nav-caption">WORKSPACE</span>
 <nav class="sidebar-nav" aria-label="Main navigation">
  <a class="{{ request()->is('dashboard')&&$navView==='overview'?'active':'' }}" href="/dashboard"><x-icon name="grid"/>Overview</a>
  <a class="{{ request()->is('/')?'active':'' }}" href="/"><x-icon name="search"/>Discover jobs<span class="nav-new">NEW</span></a>
  @can('create jobs')<a class="{{ request()->is('dashboard')&&$navView==='jobs'?'active':'' }}" href="/dashboard?view=jobs"><x-icon name="briefcase"/>Job listings</a><a class="{{ request()->is('jobs/create')?'active':'' }}" href="/jobs/create"><x-icon name="plus"/>Post a job</a>@endcan
  @can('apply jobs')<a class="{{ request()->is('dashboard')&&$navView==='applications'?'active':'' }}" href="/dashboard?view=applications"><x-icon name="file"/>My applications</a><a class="{{ request()->is('dashboard')&&$navView==='saved'?'active':'' }}" href="/dashboard?view=saved"><x-icon name="bookmark"/>Saved jobs</a>@endcan
  @can('manage platform')<a class="{{ request()->is('admin')?'active':'' }}" href="/admin"><x-icon name="shield"/>Admin control center</a>@endcan
 </nav>
 <span class="nav-caption">PERSONAL</span>
 <nav class="sidebar-nav" aria-label="Account navigation"><a class="{{ request()->is('profile')?'active':'' }}" href="/profile"><x-icon name="user"/>My profile</a><a class="{{ request()->is('dashboard')&&$navView==='notifications'?'active':'' }}" href="/dashboard?view=notifications"><x-icon name="bell"/>Notifications @if(auth()->user()->unreadNotifications()->count())<span class="nav-count">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</a></nav>
 <div class="sidebar-promo"><span class="promo-orb"><x-icon name="spark"/></span><strong>{{ auth()->user()->can('create jobs')?'Great teams start here.':'Your next move matters.' }}</strong><p>{{ auth()->user()->can('create jobs')?'Share a role. Meet your next great teammate.':'A complete profile helps employers get to know you.' }}</p><a href="{{ auth()->user()->can('create jobs')?'/jobs/create':'/profile' }}">{{ auth()->user()->can('create jobs')?'Create an opportunity':'Complete your profile' }} <x-icon name="arrow"/></a></div>
 <div class="sidebar-account"><span class="user-initial">{{ mb_substr(auth()->user()->name,0,1) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->getRoleNames()->first() }}</small></div><form method="post" action="/logout">@csrf<button class="icon-button" aria-label="Log out" title="Log out"><x-icon name="logout"/></button></form></div>
</aside>
<div class="sidebar-scrim" data-close-menu></div>
<div class="workspace-body">
<header class="workspace-topbar"><div class="d-flex align-items-center gap-3"><button class="icon-button mobile-menu" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sidebar" data-menu-toggle><x-icon name="menu"/></button><span class="breadcrumb-label">Workspace <span>/</span> <strong>{{ request()->is('/')?'Discover jobs':(request()->is('admin')?'Admin center':(request()->is('profile')?'My profile':(request()->is('dashboard')?ucfirst($navView):'Opportunities'))) }}</strong></span></div><div class="topbar-actions"><span class="today-label"><x-icon name="calendar"/>{{ today()->format('D, d M Y') }}</span><a class="notification-button icon-button" href="/dashboard?view=notifications" aria-label="Notifications"><x-icon name="bell"/>@if(auth()->user()->unreadNotifications()->exists())<i></i>@endif</a><a href="/profile" class="user-initial small-initial" aria-label="My profile">{{ mb_substr(auth()->user()->name,0,1) }}</a></div></header>
@else
<header class="site-header"><nav class="container d-flex align-items-center justify-content-between flex-wrap gap-3 py-3"><a class="brand" href="/"><span class="brand-symbol">t<span>↗</span></span>TalentSync<span class="brand-period">.</span></a><div class="d-flex gap-4 align-items-center flex-wrap"><a href="/">Find a job</a><a href="/register">For employers</a><a href="/login">Log in</a><a class="btn btn-dark" href="/register">Get started <x-icon name="arrow"/></a></div></nav></header>
@endauth
<main class="{{ auth()->check()?'workspace-main':'container public-main' }}">
@if(session('success'))<div class="alert alert-success d-flex align-items-center gap-2" role="status"><x-icon name="check"/>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Please check the following:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main>
<footer class="{{ auth()->check()?'workspace-footer':'container footer' }}"><span>© {{ date('Y') }} TalentSync</span><span>Connecting ambition with opportunity.</span></footer>
@auth</div>@endauth
<div id="live-alert" class="live-alert" role="status" hidden></div>
<script src="{{ asset('js/app.js') }}?v=2"></script>
@auth @if(config('broadcasting.default')==='pusher')<script src="{{ asset('vendor/pusher.min.js') }}"></script><script src="{{ asset('vendor/echo.iife.js') }}"></script><script>window.Echo=new Echo({broadcaster:'pusher',key:@json(config('broadcasting.connections.pusher.key')),cluster:@json(config('broadcasting.connections.pusher.options.cluster')),forceTLS:true,authEndpoint:'/broadcasting/auth',auth:{headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}}});window.Echo.private('App.Models.User.{{ auth()->id() }}').notification(n=>{const box=document.getElementById('live-alert');box.textContent=n.message;box.hidden=false;});</script>@endif @endauth
@stack('scripts')
</body></html>
