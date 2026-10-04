// packages/truvoicer/tf-perspectives/resources/js/hooks/useClipboard.ts
import { useCallback, useState } from 'react';

export function useClipboard(resetMs = 1500) {
    const [copied, setCopied] = useState(false);

    const copy = useCallback(
        async (text: string) => {
            try {
                await navigator.clipboard.writeText(text);
                setCopied(true);
                setTimeout(() => setCopied(false), resetMs);
            } catch {
                /* ignore */
            }
        },
        [resetMs],
    );

    return { copied, copy };
}
