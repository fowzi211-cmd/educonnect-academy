// Livewire's wire:navigate prefetches links on hover/viewport intersection and aborts
// the fetch when it's superseded — that abort surfaces as an uncaught promise rejection
// rather than being handled internally. It's harmless but drowns out real errors in the console.
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason?.isFromCancelledTransition) {
        event.preventDefault();
    }
});

import './protected-content';

// Arabic UI font, bundled with the app (no outside font service). Only the Arabic
// glyphs are included; Latin text keeps using Figtree.
import '@fontsource/ibm-plex-sans-arabic/arabic-400.css';
import '@fontsource/ibm-plex-sans-arabic/arabic-500.css';
import '@fontsource/ibm-plex-sans-arabic/arabic-600.css';
import '@fontsource/ibm-plex-sans-arabic/arabic-700.css';
