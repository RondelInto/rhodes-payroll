public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    // ✅ Always go to dashboard, ignore any saved intended URL
    return redirect()->route('dashboard');
}