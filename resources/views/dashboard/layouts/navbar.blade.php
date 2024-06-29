<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                {{__('translate.'.app()->getLocale())}}
            </a>
            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                <a href="{{ route(Route::getCurrentRoute()->getName(), array_merge(Route::getCurrentRoute()->parameters, ['locale' => 'ar'])) }}"
                   class="dropdown-item">
                    {{__('translate.ar')}}
                </a>
                <a href="{{ route(Route::getCurrentRoute()->getName(), array_merge(Route::getCurrentRoute()->parameters, ['locale' => "en"])) }}"
                   class="dropdown-item">
                    {{__('translate.en')}}
                </a>
            </div>
        </li>

        <!-- Settings Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-cog"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                <a href="{{route('logout',app()->getLocale())}}" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> {{__('translate.LogOut')}}
                </a>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>

    </ul>
</nav>
