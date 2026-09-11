@extends(Auth::check() ? 'layouts.app' : 'layouts.auth')

@section('title', __('messages.access_denied'))

@section('content')
<div class="container-fluid" style="padding-top: 40px; padding-bottom: 60px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 text-center">
            <div style="background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01); border: 1px solid #e5e7eb; padding: 48px 32px; margin: 20px auto;">
                <div style="width: 88px; height: 88px; background-color: #fee2e2; color: #ef4444; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                    <i class="fa fa-lock" style="font-size: 42px;" aria-hidden="true"></i>
                </div>

                <h2 style="font-size: 28px; font-weight: 700; color: #1f2937; margin-top: 0; margin-bottom: 12px; line-height: 1.3;">
                    403 | Akses Ditolak
                </h2>

                @php
                    $exception_msg = isset($exception) ? $exception->getMessage() : '';
                    if (empty($exception_msg) || strtolower(trim($exception_msg)) == 'unauthorized action.' || strtolower(trim($exception_msg)) == 'this action is unauthorized.') {
                        $display_msg = 'Maaf, Anda tidak memiliki izin atau wewenang untuk mengakses halaman atau melakukan aksi ini. Silakan hubungi Administrator jika Anda memerlukan akses.';
                    } else {
                        $display_msg = $exception_msg;
                    }
                @endphp

                <p style="font-size: 15px; color: #4b5563; max-width: 540px; margin: 0 auto 32px auto; line-height: 1.6;">
                    {{ $display_msg }}
                </p>

                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 12px;">
                    <a href="{{ action([\App\Http\Controllers\HomeController::class, 'index']) }}" class="btn btn-primary btn-lg" style="border-radius: 8px; padding: 10px 24px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin: 4px;">
                        <i class="fa fa-home" style="font-size: 16px;"></i> Kembali ke Dashboard
                    </a>

                    <button onclick="window.history.back();" class="btn btn-default btn-lg" style="border-radius: 8px; padding: 10px 24px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; background-color: #f3f4f6; border-color: #d1d5db; color: #374151; margin: 4px;">
                        <i class="fa fa-arrow-left" style="font-size: 16px;"></i> Kembali ke Halaman Sebelumnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
