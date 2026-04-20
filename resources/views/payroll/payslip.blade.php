<x-app-layout>
    <div class="max-w-4xl mx-auto">
        @include('payroll.payslip-printable')
        <div class="text-center mt-6">
            <a href="{{ url()->previous() }}" class="btn-secondary">Back</a>
            <button onclick="window.print()" class="btn-primary ml-3">Print</button>
        </div>
    </div>
</x-app-layout>