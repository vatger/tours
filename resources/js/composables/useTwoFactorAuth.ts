import { ref } from 'vue';

async function fetchApi<T>(url: string): Promise<T> {
    const response = await fetch(url, {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Failed to fetch: ${response.status}`);
    }

    return response.json();
}

export const useTwoFactorAuth = () => {
    const qrCodeSvg = ref<string | null>(null);
    const manualSetupKey = ref<string | null>(null);
    const recoveryCodesList = ref<string[]>([]);

    const fetchQrCode = async (): Promise<void> => {
        const { svg } = await fetchApi<{ svg: string; url: string }>(route('two-factor.qr-code'));
        qrCodeSvg.value = svg;
    };

    const fetchSetupKey = async (): Promise<void> => {
        const { secretKey } = await fetchApi<{ secretKey: string }>(route('two-factor.secret-key'));
        manualSetupKey.value = secretKey;
    };

    const fetchRecoveryCodes = async (): Promise<void> => {
        try {
            recoveryCodesList.value = await fetchApi<string[]>(route('two-factor.recovery-codes'));
        } catch (error) {
            console.error('Failed to fetch recovery codes:', error);
            recoveryCodesList.value = [];
        }
    };

    const fetchSetupData = async (): Promise<void> => {
        try {
            await Promise.all([fetchQrCode(), fetchSetupKey()]);
        } catch (error) {
            console.error('Failed to fetch setup data:', error);
            qrCodeSvg.value = null;
            manualSetupKey.value = null;
        }
    };

    return {
        qrCodeSvg,
        manualSetupKey,
        recoveryCodesList,
        fetchQrCode,
        fetchSetupKey,
        fetchSetupData,
        fetchRecoveryCodes,
    };
};
