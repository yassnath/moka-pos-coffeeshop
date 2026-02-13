<x-guest-layout>
    <div
        x-data="loginPage()"
        x-init="init()"
    >
        <div class="mb-6">
            <h1 class="font-display text-2xl font-bold text-moka-ink">Masuk ke Moka Kasir</h1>
            <p class="mt-1 text-sm text-moka-muted">Gunakan akun admin atau kasir untuk mulai operasional.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form
            x-ref="loginForm"
            method="POST"
            action="{{ route('login') }}"
            class="space-y-4"
            novalidate
            @submit.prevent="submitLogin"
        >
            @csrf

            <div>
                <x-input-label for="email" :value="'Email'" />
                <x-text-input
                    id="email"
                    class="mt-1 block w-full"
                    type="email"
                    name="email"
                    x-model="email"
                    :value="old('email')"
                    autofocus
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="password" :value="'Password'" />
                <div class="relative mt-1">
                    <x-text-input
                        id="password"
                        class="block w-full pr-11"
                        type="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        name="password"
                        x-model="password"
                        autocomplete="current-password"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 inline-flex w-10 items-center justify-center text-moka-muted transition hover:text-moka-ink"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                    >
                        <svg x-show="!showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                            <circle cx="12" cy="12" r="3" stroke-width="1.8"></circle>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M3 3l18 18" stroke-width="1.8" stroke-linecap="round"></path>
                            <path d="M10.6 10.6a2 2 0 002.8 2.8" stroke-width="1.8" stroke-linecap="round"></path>
                            <path d="M9.9 5.1A11.2 11.2 0 0112 5c6.5 0 10 7 10 7a17.8 17.8 0 01-4.2 4.8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M6.3 6.3C3.8 8.1 2 12 2 12s3.5 6 10 6c1.4 0 2.6-.2 3.8-.6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <label for="remember_me" class="inline-flex items-center gap-2">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-moka-line text-moka-primary focus:ring-moka-primary/40"
                    name="remember"
                    x-model="remember"
                >
                <span class="text-sm text-moka-muted">Ingat saya</span>
            </label>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-moka-primary transition hover:text-moka-ink" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif

                <x-primary-button class="w-full justify-center sm:w-auto" x-bind:disabled="isSubmitting">
                    <span x-show="!isSubmitting">Masuk</span>
                    <span x-show="isSubmitting">Memproses...</span>
                </x-primary-button>
            </div>
        </form>

        <x-ui.modal name="alertOpen" maxWidth="md">
            <div class="moka-modal-content">
                <div class="moka-modal-header">
                    <div>
                        <h3 class="moka-modal-title" x-text="alertTitle"></h3>
                        <p class="moka-modal-subtitle" x-text="alertSubtitle()"></p>
                    </div>
                    <button type="button" class="moka-modal-close" @click="closeAlert()" aria-label="Tutup popup">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M6 6l12 12M18 6l-12 12" stroke-width="1.8" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </div>

                <div class="moka-modal-alert" :class="alertToneClass()">
                    <p x-text="alertMessage"></p>
                </div>

                <div class="moka-modal-footer">
                    <button type="button" class="moka-btn-secondary" @click="closeAlert()">Tutup</button>
                    <button type="button" class="moka-btn" @click="closeAlert()">OK</button>
                </div>
            </div>
        </x-ui.modal>
    </div>

    <script>
        function loginPage() {
            return {
                email: @js(old('email', '')),
                password: '',
                showPassword: false,
                remember: @js((bool) old('remember', false)),
                isSubmitting: false,
                alertOpen: false,
                alertType: 'info',
                alertTitle: '',
                alertMessage: '',
                alertTimer: null,
                alertAfterCloseAction: null,

                init() {
                    @if ($errors->any())
                        this.showAlert(
                            'Login gagal',
                            'username / password belum sesuai, mohon dicek lagi!',
                            'error'
                        );
                    @endif
                },

                alertToneClass() {
                    if (this.alertType === 'error') {
                        return 'moka-alert-error';
                    }
                    if (this.alertType === 'warning') {
                        return 'moka-alert-warning';
                    }

                    return 'moka-alert-success';
                },

                alertSubtitle() {
                    if (this.alertType === 'error') {
                        return 'Periksa kembali email dan password.';
                    }
                    if (this.alertType === 'warning') {
                        return 'Input login belum lengkap.';
                    }

                    return 'Proses login berhasil.';
                },

                closeAlert() {
                    this.alertOpen = false;

                    if (this.alertTimer) {
                        clearTimeout(this.alertTimer);
                        this.alertTimer = null;
                    }

                    const action = this.alertAfterCloseAction;
                    this.alertAfterCloseAction = null;
                    if (typeof action === 'function') {
                        action();
                    }
                },

                showAlert(title, message, type = 'success', options = {}) {
                    if (this.alertTimer) {
                        clearTimeout(this.alertTimer);
                        this.alertTimer = null;
                    }

                    this.alertTitle = title;
                    this.alertMessage = message;
                    this.alertType = type;
                    this.alertAfterCloseAction = options.afterClose ?? null;
                    this.alertOpen = true;

                    if (options.autoCloseMs) {
                        this.alertTimer = setTimeout(() => {
                            this.closeAlert();
                        }, options.autoCloseMs);
                    }
                },

                hasRequiredError(errors) {
                    if (!Array.isArray(errors)) {
                        return false;
                    }

                    return errors.some((message) => /(required|wajib|harus diisi|field is required)/i.test(String(message)));
                },

                async submitLogin() {
                    const normalizedEmail = this.email.trim();
                    const normalizedPassword = this.password.trim();

                    if (normalizedEmail === '' && normalizedPassword === '') {
                        this.showAlert('Validasi login', 'mohon diisi terlebih dahulu email dan passwordnya!', 'warning');
                        return;
                    }

                    if (normalizedEmail === '') {
                        this.showAlert('Validasi login', 'mohon isi email terlebih dahulu!', 'warning');
                        return;
                    }

                    if (normalizedPassword === '') {
                        this.showAlert('Validasi login', 'mohon isi password terlebih dahulu!', 'warning');
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        const formData = new FormData(this.$refs.loginForm);
                        const response = await fetch(this.$refs.loginForm.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: formData,
                            credentials: 'same-origin',
                        });

                        if (response.status === 422) {
                            const data = await response.json();
                            const emailErrors = data.errors?.email ?? [];
                            const passwordErrors = data.errors?.password ?? [];

                            if (this.hasRequiredError(emailErrors) && this.hasRequiredError(passwordErrors)) {
                                this.showAlert('Validasi login', 'mohon diisi terlebih dahulu email dan passwordnya!', 'warning');
                                return;
                            }

                            if (this.hasRequiredError(emailErrors)) {
                                this.showAlert('Validasi login', 'mohon isi email terlebih dahulu!', 'warning');
                                return;
                            }

                            if (this.hasRequiredError(passwordErrors)) {
                                this.showAlert('Validasi login', 'mohon isi password terlebih dahulu!', 'warning');
                                return;
                            }

                            this.showAlert('Login gagal', 'username / password belum sesuai, mohon dicek lagi!', 'error');
                            return;
                        }

                        if (response.redirected) {
                            this.showAlert('Login', 'berhasil login!', 'success', {
                                autoCloseMs: 3000,
                                afterClose: () => {
                                    window.location.href = response.url;
                                },
                            });
                            return;
                        }

                        if (response.ok) {
                            this.showAlert('Login', 'berhasil login!', 'success', {
                                autoCloseMs: 3000,
                                afterClose: () => {
                                    window.location.href = @js(route('dashboard'));
                                },
                            });
                            return;
                        }

                        this.showAlert('Login gagal', 'Terjadi kesalahan saat login. Coba lagi.', 'error');
                    } catch (error) {
                        this.showAlert('Koneksi gagal', 'Tidak dapat menghubungi server. Periksa koneksi lalu coba lagi.', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                },
            };
        }
    </script>
</x-guest-layout>

