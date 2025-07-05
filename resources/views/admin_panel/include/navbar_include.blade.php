<div class="header">
	<div class="header-left active">
		<a href="#" class="logo">
			<img src="{{ url('logo.jpeg') }}" alt="">
		</a>
		<a href="#" class="logo-small">
			<img src="{{ url('logo.jpeg') }}" alt="">
		</a>
		<a id="toggle_btn" href="javascript:void(0);">
		</a>
	</div>
	<a id="mobile_btn" class="mobile_btn" href="#sidebar">
		<span class="bar-icon">
			<span></span>
			<span></span>
			<span></span>
		</span>
	</a>

	<ul class="nav user-menu">
		<li class="nav-item">
			<div class="top-nav-search">
				<a href="javascript:void(0);" class="responsive-search">
					<i class="fa fa-search"></i>
				</a>
				<form action="#">
					<div class="searchinputs">
						<input type="text" placeholder="Search Here ...">
						<div class="search-addon">
							<span><img src="{{ url('assets/img/icons/closes.svg') }}" alt="img"></span>
						</div>
					</div>
					<a class="btn" id="searchdiv"><img src="{{ url('assets/img/icons/search.svg') }}" alt="img"></a>
				</form>
			</div>
		</li>
		<li class="nav-item dropdown has-arrow main-drop">
			<a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
				<span class="user-img"><img src="{{ url('assets/img/profiles/manager.png') }}" alt="">
					<span class="status online"></span></span>
			</a>
			<div class="dropdown-menu menu-drop-user">
				<div class="profilename">
					<div class="profileset">
						<span class="user-img"><img src="{{ url('assets/img/profiles/manager.png') }}" alt="">
							<span class="status online"></span></span>
						<div class="profilesets">
							<h6>{{ Auth::user()->name }}</h6>
							<h5>Admin</h5>
						</div>
					</div>
					<hr class="m-0">
					<hr class="m-0">
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="dropdown-item ai-icon"> Logout </a>
					</form>
				</div>
			</div>
		</li>
	</ul>

	<div class="dropdown mobile-user-menu">
		<a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
		<div class="dropdown-menu dropdown-menu-right">
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="dropdown-item ai-icon"> Logout </a>
			</form>
		</div>
	</div>
</div>