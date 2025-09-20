<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Demo Task</title>
  <link rel="stylesheet" href="{{ asset('Dashboard/css/main.css') }}" />
  <style>

</style>
</head>

<body>
  <main class="card" aria-label="Login form">
    <div class="brand">
      <h1>Welcome back</h1>
    </div>
    <p class="subtitle">Sign in to continue</p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" class="@error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus> @error('email')
            <div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" class="@error('password') is-invalid @enderror" name="password" required> @error('password')
            <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn">Login</button>
    </form>
  </main>
</body>
</html>
