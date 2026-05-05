<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Profile</h1>
        
        {{-- Profile Information Update --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Information</h2>
            <form method="POST" action="{{ route('my.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="input-label">Full Name</label>
                        <input type="text" value="{{ $employee->full_name }}" disabled class="input-field bg-gray-100 dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="input-label">Email</label>
                        <input type="email" value="{{ $employee->email }}" disabled class="input-field bg-gray-100 dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="input-label">Phone</label>
                        <input type="text" name="phone" value="{{ $employee->phone }}" required class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Address</label>
                        <textarea name="address" class="input-field">{{ $employee->address }}</textarea>
                    </div>
                    <div>
                        <label class="input-label">City</label>
                        <input type="text" name="city" value="{{ $employee->city }}" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Province</label>
                        <input type="text" name="province" value="{{ $employee->province }}" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Zip Code</label>
                        <input type="text" name="zip_code" value="{{ $employee->zip_code }}" class="input-field">
                    </div>
                    <button type="submit" class="btn-primary w-full">Update Profile</button>
                </div>
            </form>
        </div>

        {{-- Change Password Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h2>
            <form method="POST" action="{{ route('my.password.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="input-label">Current Password</label>
                        <input type="password" name="current_password" required class="input-field">
                        @error('current_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="input-label">New Password</label>
                        <input type="password" name="password" required class="input-field">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="input-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required class="input-field">
                    </div>
                    <button type="submit" class="btn-primary w-full">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>