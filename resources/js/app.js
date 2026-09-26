// Livewire's wire:navigate prefetches links on hover/viewport intersection and aborts
// the fetch when it's superseded — that abort surfaces as an uncaught promise rejection
// rather than being handled internally. It's harmless but drowns out real errors in the console.
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason?.isFromCancelledTransition) {
        event.preventDefault();
    }
});
