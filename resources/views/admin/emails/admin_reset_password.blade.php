@component('mail::message')
# Reset Account

Welcome {{$thedata['data']->name}}

@component('mail::button', ['url' => aurl('reset/password/'. $thedata['token'])])
Click Here To Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
