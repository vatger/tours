export const useTwoFactorAuth = () => {
    const fetchQRCode = async (): Promise<{ svg: string }> => {
        const response = await fetch(route('two-factor.qr-code'));
        return await response.json();
    };

    const fetchManualSetupKey = async (): Promise<{ secretKey: string }> => {
        const response = await fetch(route('two-factor.secret-key'));
        return await response.json();
    };

    const fetchRecoveryCodes = async (): Promise<string[]> => {
        const response = await fetch(route('two-factor.recovery-codes'));
        return await response.json();
    };

    return {
        fetchQRCode,
        fetchManualSetupKey,
        fetchRecoveryCodes,
    };
};