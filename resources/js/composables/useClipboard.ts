import { ref } from 'vue';

interface ClipboardOptions {
    timeout?: number;
}

export function useClipboard(options: ClipboardOptions = {}) {
    const { timeout = 1500 } = options;
    
    const copied = ref(false);
    
    const copyToClipboard = async (text: string): Promise<void> => {
        if (typeof window === 'undefined' || !navigator.clipboard) {
            return;
        }
        
        try {
            await navigator.clipboard.writeText(text);
            copied.value = true;
            setTimeout(() => (copied.value = false), timeout);
        } catch (error) {
            console.error('Failed to copy to clipboard:', error);
        }
    };
    
    return {
        copied,
        copyToClipboard,
    };
}