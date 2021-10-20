@component('mail::message')
# Introduction

{{$username}}

The body of your message.

@component('mail::button', ['url' => ''])
Verify Mail
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
