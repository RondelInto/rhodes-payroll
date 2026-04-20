<x-app-layout>
    <div class="flex flex-col items-center justify-center py-20">
        <i class="fas fa-map-signs text-7xl text-slate-400 mb-4"></i>
        <h1 class="text-4xl font-bold text-slate-800 dark:text-white">404</h1>
        <p class="text-slate-600 dark:text-slate-300 mt-2">Page not found.</p>
        <a href="{{ route('dashboard') }}" class="btn-primary mt-6">
            <i class="fas fa-home mr-2"></i> Back to Dashboard
        </a>
    </div>
</x-app-layout>