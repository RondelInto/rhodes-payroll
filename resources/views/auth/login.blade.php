<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rhodes Payroll | Sign In</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-900 dark:to-slate-800">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-6xl w-full flex flex-col md:flex-row bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden">
            {{-- Left side - branding --}}
            <div class="md:w-1/2 bg-gradient-to-br from-blue-700 to-indigo-800 p-8 text-white flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-2 mb-8">
                        <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                            <i class="fas fa-chalkboard-user text-2xl"></i>
                        </div>
                        <span class="text-2xl font-bold tracking-tight">Rhodes Payroll</span>
                    </div>
                    <h2 class="text-3xl font-bold mt-8">Welcome back</h2>
                    <p class="text-blue-100 mt-2">Sign in to manage your payroll system</p>
                </div>
                <div class="mt-8 space-y-4">
                    <div class="flex items-center space-x-3"><i class="fas fa-check-circle text-blue-300"></i><span>SSS, PhilHealth, Pag-IBIG compliant</span></div>
                    <div class="flex items-center space-x-3"><i class="fas fa-check-circle text-blue-300"></i><span>TRAIN Law withholding tax</span></div>
                    <div class="flex items-center space-x-3"><i class="fas fa-check-circle text-blue-300"></i><span>Automated payslips & reports</span></div>
                </div>
                <div class="mt-8 text-sm text-blue-200">© 2025 Rhodes Corporation. All rights reserved.</div>
            </div>

            {{-- Right side - login form --}}
            <div class="md:w-1/2 p-8 md:p-12">
                <div class="max-w-sm mx-auto">
                    <div class="text-center mb-8">
                        <i class="fas fa-lock text-4xl text-blue-600 mb-3"></i>
                        <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Sign In</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Enter your credentials to access your account</p>
                    </div>
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full pl-10 pr-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700">
                            </div>
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password</label>
                            <div class="relative">
                                <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                                <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 border border-slate-300 dark:border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700">
                            </div>
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center"><input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"><span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Remember me</span></label>
                            <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 rounded-xl shadow-lg transition-all duration-200">Sign In</button>
                    </form>
                    <div class="mt-6 text-center text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                        Demo: admin@rhodes.com / password
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>