@component('mail::message')
Hi, {{ $user->name }}

請點選下方按鈕啟用帳號！

@component('mail::button', ['url' => route('email-verifications.update', $emailVerification->token)])
    啟用帳號
@endcomponent
@endcomponent
