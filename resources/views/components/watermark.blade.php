@php
    $viewer = auth()->user();
    $label = $viewer ? $viewer->name.' · '.$viewer->email.' · '.now()->format('Y-m-d H:i') : '';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="460" height="220">'
        .'<text x="230" y="110" text-anchor="middle" transform="rotate(-25 230 110)" '
        .'font-family="sans-serif" font-size="15" font-weight="600" fill="rgba(128,128,128,0.38)">'
        .htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</text></svg>';
    $uri = 'data:image/svg+xml;utf8,'.rawurlencode($svg);
@endphp
@if ($viewer)
    <div class="pointer-events-none absolute inset-0 z-20 select-none" aria-hidden="true"
         style="background-image: url('{{ $uri }}'); background-repeat: repeat;"></div>
@endif
