<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="{{url('images/logo.png')}}" alt="AdminLTE Logo"
             class="brand-image"
             >
        <span class="brand-text font-weight-light">Train Track</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar users panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{asset('dashboard/dist/img/avatar5.png')}}" class="img-circle elevation-2"
                     alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{Auth::guard('admin')->user()->name}}</a>
            </div>
        </div>


        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->


                <li class="nav-item">
                    <a href="{{route('coaches.index',app()->getLocale())}}"
                       class="nav-link {{str_contains(Route::currentRouteName(), 'coaches')?"active":""}}">
                        <i class="nav-icon fas fa-user-alt"></i>
                        <p>
                            {{__('translate.Coaches')}}
                        </p>
                    </a>

                </li>
                <li class="nav-item">
                    <a href="{{route('payments.index',app()->getLocale())}}"
                       class="nav-link {{str_contains(Route::currentRouteName(), 'payments')?"active":""}}"">
                    <i class="nav-icon far fa-credit-card"></i>

                    <p>
                        {{__('translate.CoachesPayments')}}
                    </p>
                    </a>

                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
