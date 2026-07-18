<x-mail::message>
# New Contact Message

**From:** {{ $senderName }} ({{ $senderEmail }})

**Subject:** {{ $subjectLine }}

{{ $body }}

<x-mail::button :url="'mailto:'.$senderEmail">
Reply
</x-mail::button>

Thanks,<br>
{{ \App\Models\Setting::get('branding.platform_name', config('app.name')) }}
</x-mail::message>
