@extends(Auth::check() ? 'layouts.app' : 'layouts.auth')

@section('title', __('messages.access_denied'))

@section('content')
<div class="container-fluid tw-py-8">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center">
            <div class="tw-bg-white tw-rounded-2xl tw-shadow-sm tw-ring-1 tw-ring-gray-200 tw-p-8 md:tw-p-12 tw-my-6">
                <div class="tw-inline-flex tw-items-center tw-justify-center tw-w-20 tw-h-20 tw-bg-red-50 tw-text-red-500 tw-rounded-full tw-mb-6">
                    <i class="fa fa-lock fa-3x" aria-hidden="true"></i>
                </div>

                <h1 class="tw-text-3xl tw-font-bold tw-text-gray-800 tw-mb-3">
                    403 | Akses Ditolak
                </h1>

                <p class="tw-text-base tw-text-gray-600 tw-max-w-md tw-mx-auto tw-mb-8">
                    {{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki izin atau wewenang untuk mengakses halaman ini. Silakan hubungi Administrator jika Anda merasa ini adalah kesalahan.' }}
                </p>

                <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-center tw-gap-4">
                    <a href="{{ action([\App\Http\Controllers\HomeController::class, 'index']) }}" class="btn btn-primary btn-lg tw-rounded-xl tw-px-6">
                        <i class="fa fa-home tw-mr-2"></i> Kembali ke Dashboard
                    </a>

                    <button onclick="window.history.back();" class="btn btn-default btn-lg tw-rounded-xl tw-px-6">
                        <i class="fa fa-arrow-left tw-mr-2"></i> Kembali ke Halaman Sebelumnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
