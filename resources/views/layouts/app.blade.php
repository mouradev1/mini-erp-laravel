<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Mini ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('produtos.index') }}">Mini ERP</a>
            <div>
                <ul class="navbar-nav">
                    <li class="nav-item"><a href="{{ route('produtos.index') }}" class="nav-link">Produtos</a></li>
                    <li class="nav-item"><a href="{{ route('pedido.carrinho') }}" class="nav-link">Carrinho</a></li>
                    <li class="nav-item"><a href="{{ route('cupons.index') }}" class="nav-link">Cupons</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        @yield('content')
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</body>

</html>