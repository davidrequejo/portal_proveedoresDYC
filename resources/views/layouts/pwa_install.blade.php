<button type="button" id="pwa-install-button" class="pwa-install-button" hidden>
    Instalar aplicacion
</button>

<div id="pwa-install-message" class="pwa-install-message" role="status" aria-live="polite" hidden></div>

<style>
    .pwa-install-button {
        position: fixed;
        right: 18px;
        bottom: 18px;
        z-index: 2147483000;
        border: 0;
        border-radius: 999px;
        padding: 12px 18px;
        background: #0b2f4f;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
        box-shadow: 0 10px 25px rgba(11, 47, 79, 0.28);
        cursor: pointer;
    }

    .pwa-install-button:hover {
        background: #123f66;
    }

    .pwa-install-button:disabled {
        cursor: wait;
        opacity: 0.78;
    }

    .pwa-install-message {
        position: fixed;
        left: 50%;
        bottom: 78px;
        z-index: 2147483000;
        transform: translateX(-50%);
        max-width: calc(100vw - 32px);
        border-radius: 8px;
        padding: 11px 16px;
        background: rgba(17, 24, 39, 0.96);
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
    }

    @media (display-mode: standalone) {
        .pwa-install-button {
            display: none !important;
        }
    }
</style>

<script>
    (() => {
        const installButton = document.getElementById('pwa-install-button');
        const installMessage = document.getElementById('pwa-install-message');
        let deferredInstallPrompt = null;
        let messageTimer = null;

        const isStandalone = () => {
            return window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true;
        };

        const hideInstallButton = () => {
            if (installButton) {
                installButton.hidden = true;
                installButton.disabled = false;
            }
        };

        const showMessage = (message, timeout = 3000) => {
            if (!installMessage) {
                return;
            }

            window.clearTimeout(messageTimer);
            installMessage.textContent = message;
            installMessage.hidden = false;

            if (timeout > 0) {
                messageTimer = window.setTimeout(() => {
                    installMessage.hidden = true;
                }, timeout);
            }
        };

        if ('serviceWorker' in navigator && window.isSecureContext) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js', { scope: '/' }).catch((error) => {
                    console.warn('No se pudo registrar el Service Worker.', error);
                });
            });
        }

        if (isStandalone() || localStorage.getItem('pwa-installed') === 'yes') {
            hideInstallButton();
            return;
        }

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;

            if (installButton && !isStandalone()) {
                installButton.hidden = false;
            }
        });

        installButton?.addEventListener('click', async () => {
            if (!deferredInstallPrompt) {
                hideInstallButton();
                return;
            }

            installButton.disabled = true;
            showMessage('Instalando aplicacion...', 0);

            deferredInstallPrompt.prompt();
            const choice = await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;

            if (choice.outcome === 'accepted') {
                localStorage.setItem('pwa-installed', 'yes');
                showMessage('Aplicacion instalada correctamente');
                hideInstallButton();
                return;
            }

            installButton.disabled = false;
            installMessage.hidden = true;
        });

        window.addEventListener('appinstalled', () => {
            deferredInstallPrompt = null;
            localStorage.setItem('pwa-installed', 'yes');
            showMessage('Aplicacion instalada correctamente');
            hideInstallButton();
        });
    })();
</script>
