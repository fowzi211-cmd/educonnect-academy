// View-only course content: blocks the easy ways to save or copy what is on screen.
// This is a deterrent, not DRM. Nothing in a browser can stop an operating-system
// screenshot, a phone camera, or screen-recording software; the visible watermark
// is what makes any such copy traceable.

const isField = (el) => !!el?.closest?.('input, textarea, select, [contenteditable="true"]');

document.addEventListener('alpine:init', () => {
    window.Alpine.data('protectedArea', () => ({
        covered: false,
        fullscreenActive: false,
        handlers: [],

        init() {
            const on = (target, type, fn, opts) => {
                target.addEventListener(type, fn, opts);
                this.handlers.push(() => target.removeEventListener(type, fn, opts));
            };
            const el = this.$el;

            on(el, 'contextmenu', (e) => e.preventDefault());
            on(el, 'dragstart', (e) => e.preventDefault());
            on(el, 'copy', (e) => { if (!isField(e.target)) e.preventDefault(); });
            on(el, 'cut', (e) => { if (!isField(e.target)) e.preventDefault(); });
            on(el, 'selectstart', (e) => { if (!isField(e.target)) e.preventDefault(); });

            on(window, 'keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && ['s', 'p', 'u'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                }
                if ((e.ctrlKey || e.metaKey) && ['c', 'x', 'a'].includes(e.key.toLowerCase()) && !isField(e.target)) {
                    e.preventDefault();
                }
            });

            // PrintScreen cannot be blocked, but clearing the clipboard defeats the simplest capture.
            on(window, 'keyup', (e) => {
                if (e.key === 'PrintScreen') {
                    navigator.clipboard?.writeText('').catch(() => {});
                    this.cover();
                }
            });

            on(window, 'blur', () => this.cover());
            on(window, 'focus', () => { this.covered = false; });
            on(document, 'visibilitychange', () => { if (document.hidden) this.cover(); });
            on(document, 'fullscreenchange', () => {
                this.fullscreenActive = document.fullscreenElement === el;
            });
        },

        cover() {
            this.covered = true;
            this.$el.querySelectorAll('video, audio').forEach((m) => m.pause());
        },

        // Fullscreen the whole protected block (not the bare <video>) so the watermark stays visible.
        toggleFullscreen() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                this.$el.requestFullscreen?.();
            }
        },

        destroy() {
            this.handlers.forEach((off) => off());
            this.handlers = [];
        },
    }));

    window.Alpine.data('pdfViewer', (url) => ({
        loading: true,
        failed: false,

        async init() {
            try {
                const pdfjs = await import('pdfjs-dist');
                const worker = (await import('pdfjs-dist/build/pdf.worker.min.mjs?url')).default;
                pdfjs.GlobalWorkerOptions.workerSrc = worker;

                // One plain request per document: no partial-range fetching.
                const doc = await pdfjs.getDocument({
                    url,
                    withCredentials: true,
                    disableRange: true,
                    disableStream: true,
                }).promise;
                const container = this.$refs.pages;
                const dpr = window.devicePixelRatio || 1;

                for (let n = 1; n <= doc.numPages; n++) {
                    const page = await doc.getPage(n);
                    const base = page.getViewport({ scale: 1 });
                    const scale = ((container.clientWidth || 800) / base.width) * dpr;
                    const viewport = page.getViewport({ scale });

                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    canvas.className = 'block w-full mb-3 rounded shadow bg-white';
                    container.appendChild(canvas);

                    await page.render({ canvas, canvasContext: canvas.getContext('2d'), viewport }).promise;
                }
            } catch (error) {
                console.error(error);
                this.failed = true;
            } finally {
                this.loading = false;
            }
        },
    }));
});
