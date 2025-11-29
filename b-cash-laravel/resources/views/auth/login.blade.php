<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B-Cash Login</title>

    {{-- Laravel CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(180deg, #1e9b83 0%, #2ec4b6 100%);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            width: 380px;
            max-width: 90%;
            padding: 40px 35px;
            text-align: center;
        }

        .login-wrapper h1 {
            color: #1e9b83;
            margin-bottom: 8px;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .login-wrapper p {
            color: #777;
            font-size: 0.95rem;
            margin-bottom: 25px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            color: #444;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 1rem;
            transition: 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #1e9b83;
            box-shadow: 0 0 0 2px rgba(30,155,131,0.2);
            outline: none;
        }

        button {
            width: 100%;
            background-color: #1e9b83;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            background-color: #188f73;
        }

        .alert {
            padding: 10px 15px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-align: left;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .register-text {
            font-size: 0.9rem;
            color: #444;
            margin-top: 20px;
        }

        .register-text a {
            color: #1e9b83;
            font-weight: 600;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .google-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border-radius: 10px;
            background-color: #4285F4;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .google-login:hover {
            background-color: #357ae8;
        }

        .google-login img {
            width: 20px;
            vertical-align: middle;
        }

        footer {
            position: fixed;
            bottom: 10px;
            text-align: center;
            width: 100%;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.8);
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <h1>B-Cash</h1>
        <p>Log in securely to continue</p>

        {{-- ✅ Success Message --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- ❌ Error Messages --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ✅ Login Form --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input 
                    type="text" 
                    id="phone_number" 
                    name="phone_number" 
                    value="{{ old('phone_number') }}" 
                    placeholder="09XXXXXXXXX" 
                    required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password" 
                    required>
            </div>

            <button type="submit">Log In</button>
        </form>

        {{-- Google Login --}}
        <a href="{{ route('login.google') }}" class="google-login">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Google_%22G%22_Logo.svg/512px-Google_%22G%22_Logo.svg.png" alt="Google Logo">
            Log in with Google
        </a>

        <div class="register-text">
            <p>Don’t have an account? <a href="{{ route('register') }}">Create one</a></p>
        </div>
    </div>

    <footer>
        &copy; {{ date('Y') }} B-Cash. All rights reserved.
    </footer>

</body>
</html>
