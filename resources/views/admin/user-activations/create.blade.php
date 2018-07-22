<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Dreamsgem Admin</title>
</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="{{ route('admin.home') }}">Dreamsgem</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('admin.user-activations.create') }}">小龍管理</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('admin.auth.logout') }}">登出</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <div class="row mt-3">
        <div class="col-12">
            <form method="POST" action="{{ route('admin.user-activations.store') }}">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="form-user-id">使用者 ID</label>
                    <input type="text" class="form-control" id="form-user-id" name="user_id">
                </div>
                <button type="submit" class="btn btn-primary">購買小龍給該使用者</button>
            </form>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">擁有者 ID</th>
                    <th scope="col">擁有者暱稱</th>
                    <th scope="col">激活對象 ID</th>
                    <th scope="col">激活對象暱稱</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dragons as $idx => $dragon)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ app(\App\HashidsTransformer::class)->protect($dragon->owner->id) }}</td>
                        <td>{{ $dragon->owner->name }}</td>
                        <td>{{ $dragon->user ? app(\App\HashidsTransformer::class)->protect($dragon->user->id) : '' }}</td>
                        <td>{{ optional($dragon->user)->name }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
</body>
</html>