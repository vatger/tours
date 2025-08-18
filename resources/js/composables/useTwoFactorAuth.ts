import { ref, watch } from 'vue';


interface ConfirmResponse {
    status: string;
    recovery_codes?: string[];
    message?: string;
}

interface RecoveryCodesResponse {
    recovery_codes: string[];
}

interface QrCodeResponse {
    svg: string;
}

interface SecretKeyResponse {
    secretKey: string;
}


export function useTwoFactorAuth(initialConfirmed: boolean = false, initialRecoveryCodes: string[] = []) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const headers = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
    };

    const confirmed = ref(initialConfirmed);
    const qrCodeSvg = ref('');
    const secretKey = ref('');
    const recoveryCodesList = ref(initialRecoveryCodes);
    const copied = ref(false);
    const passcode = ref('');
    const error = ref('');
    const verifyStep = ref(false);
    const showingRecoveryCodes = ref(false);
    const showModal = ref(false);
    const isLoading = ref(false);

    watch([showModal, verifyStep, qrCodeSvg], ([newShowModal, newVerifyStep, newQrCodeSvg]) => {
        if (newShowModal && !newVerifyStep && !newQrCodeSvg) {
            enable().then(() => true);
        }
    });

    const enable = async (): Promise<void> => {
        try {
            isLoading.value = true;
            error.value = '';

            const enableResponse = await fetch(route('two-factor.enable'), {
                method: 'POST',
                headers,
            });

            if (enableResponse.ok) {
                const [qrCode, secret] = await Promise.all([
                    fetchQrCode(),
                    fetchSecretKey()
                ]);

                if (qrCode && secret) {
                    qrCodeSvg.value = qrCode;
                    secretKey.value = secret;
                }
            } else {
                error.value = 'Failed to enable two-factor authentication';
            }
        } catch (err) {
            error.value = 'Error enabling two-factor authentication';
            console.error('Error enabling 2FA:', err);
        } finally {
            isLoading.value = false;
        }
    };

    const fetchQrCode = async (): Promise<string | null> => {
        try {
            const response = await fetch(route('two-factor.qr-code'), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data: QrCodeResponse = await response.json();
                return btoa(data.svg);
            }
            return null;
        } catch {
            return null;
        }
    };

    const fetchSecretKey = async (): Promise<string | null> => {
        try {
            const response = await fetch(route('two-factor.secret-key'), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data: SecretKeyResponse = await response.json();
                return data.secretKey;
            }
            return null;
        } catch {
            return null;
        }
    };

    const confirm = async (): Promise<boolean> => {
        if (!passcode.value || passcode.value.length !== 6) return false;

        const formattedCode = passcode.value.replace(/\s+/g, '').trim();

        try {
            const response = await fetch(route('two-factor.confirm'), {
                method: 'POST',
                headers,
                body: JSON.stringify({ code: formattedCode }),
            });

            if (response.ok) {
                const responseData: ConfirmResponse = await response.json();
                if (responseData.recovery_codes) {
                    recoveryCodesList.value = responseData.recovery_codes;
                }

                confirmed.value = true;
                verifyStep.value = false;
                showModal.value = false;
                showingRecoveryCodes.value = true;
                passcode.value = '';
                error.value = '';
                return true;
            } else {
                const errorData = await response.json();
                error.value = errorData.message || 'Invalid verification code';
                passcode.value = '';
                return false;
            }
        } catch (err) {
            console.error('Error confirming 2FA:', err);
            error.value = 'An error occurred while confirming 2FA';
            return false;
        }
    };

    const regenerateRecoveryCodes = async (): Promise<void> => {
        try {
            const response = await fetch(route('two-factor.recovery-codes'), {
                method: 'POST',
                headers,
            });

            if (response.ok) {
                const data: RecoveryCodesResponse = await response.json();
                if (data.recovery_codes) {
                    recoveryCodesList.value = data.recovery_codes;
                }
            } else {
                console.error('Error regenerating codes:', response.statusText);
            }
        } catch (error) {
            console.error('Error regenerating codes:', error);
        }
    };

    const disable = async (): Promise<boolean> => {
        try {
            const response = await fetch(route('two-factor.disable'), {
                method: 'DELETE',
                headers
            });

            if (response.ok) {
                confirmed.value = false;
                showingRecoveryCodes.value = false;
                recoveryCodesList.value = [];
                qrCodeSvg.value = '';
                secretKey.value = '';
                return true;
            } else {
                console.error('Error disabling 2FA:', response.statusText);
                return false;
            }
        } catch (error) {
            console.error('Error disabling 2FA:', error);
            return false;
        }
    };

    const copyToClipboard = (text: string): void => {
        navigator.clipboard.writeText(text).then(() => copied.value = true);
        setTimeout(() => copied.value = false, 1500);
    };

    return {
        confirmed,
        qrCodeSvg,
        secretKey,
        recoveryCodesList,
        copied,
        passcode,
        error,
        verifyStep,
        showingRecoveryCodes,
        showModal,
        isLoading,
        enable,
        confirm,
        regenerateRecoveryCodes,
        disable,
        copyToClipboard,
    };
}
