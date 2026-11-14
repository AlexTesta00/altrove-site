<?php
function icon(string $name): string {
    $paths=[
        'arrow'=>'<path d="M4 12h15M13 6l6 6-6 6"/>',
        'back'=>'<path d="M20 12H5M11 6l-6 6 6 6"/>',
        'mic'=>'<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5.5 11a6.5 6.5 0 0 0 13 0M12 17.5V21"/>',
        'play'=>'<path d="M8 5.5v13l10.5-6.5z"/>',
        'youtube'=>'<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10 9.5v5l4.5-2.5z"/>',
        'instagram'=>'<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1"/>',
        'pause'=>'<path d="M8 5v14M16 5v14"/>',
        'plus'=>'<path d="M5 12h14M12 5v14"/>',
        'whatsapp'=>'<path d="M20 11.5a8.5 8.5 0 0 1-12.6 7.4L3.5 20l1.2-3.7A8.5 8.5 0 1 1 20 11.5z"/>',
        'copy'=>'<rect x="8" y="8" width="12" height="13" rx="2"/><path d="M15 8V3H3v13h5"/>'
    ];
    return '<svg class="ic" viewBox="0 0 24 24" aria-hidden="true">'.($paths[$name]??$paths['arrow']).'</svg>';
}
