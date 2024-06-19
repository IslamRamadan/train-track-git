@if(Session::has('error'))
    <div class="col-xs-12">
        <div class="alert alert-danger">{{ Session::get('error') }}</div>
    </div>
@endif

@if(Session::has('msg'))
    <div class="col-xs-12">
        <div class="alert alert-success">{{ Session::get('msg') }}</div>
    </div>
@endif

@if(count($errors->all()))
    <div class="col-xs-12">
        <div class="alert alert-danger">
            <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    </div>
@endif