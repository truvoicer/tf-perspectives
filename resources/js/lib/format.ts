// packages/truvoicer/tf-perspectives/resources/js/lib/format.ts
export function timeAgo(iso: string | null): string {
    if (!iso) return '';
    const s = Math.floor((Date.now() - new Date(iso).getTime()) / 1000);
    const steps: Array<[number, string]> = [
        [31536000, 'y'], [2592000, 'mo'], [604800, 'w'],
        [86400, 'd'], [3600, 'h'], [60, 'm'],
    ];
    for (const [size, label] of steps) {
        if (s >= size) return `${Math.floor(s / size)}${label} ago`;
    }
    return 'just now';
}

export function initials(name: string | null | undefined): string {
    if (!name) return '?';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((w) => w.charAt(0).toUpperCase())
        .join('');
}

export function excerpt(text: string, max = 140): string {
    if (text.length <= max) return text;
    return text.slice(0, max).trimEnd() + '…';
}
