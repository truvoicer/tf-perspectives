export interface RouteParameter {
    /** e.g. "perspective_categories" */
    entity: string;
    /** e.g. "id" */
    column: string;
    /** raw placeholder, e.g. "{perspective_categories:id}" */
    raw: string;
}

/**
 * Parse a Laravel-style route definition and extract every {entity:column}
 * placeholder. Returns them in order of appearance.
 *
 * "/app/perspectives/category/{perspective_categories:id}"
 *   => [{ entity: "perspective_categories", column: "id", raw: "{perspective_categories:id}" }]
 */
export function parseRouteParameters(routeDefinition: string | null | undefined): RouteParameter[] {
    if (!routeDefinition) return [];

    const results: RouteParameter[] = [];
    // Match {...} placeholders; capture inner content
    const regex = /\{([^{}]+)\}/g;
    let match: RegExpExecArray | null;

    while ((match = regex.exec(routeDefinition)) !== null) {
        const inner = match[1].trim(); // "perspective_categories:id"
        const raw = match[0];          // "{perspective_categories:id}"

        // Laravel optional params can be "entity:column?" — strip trailing ?
        const withoutOptional = inner.replace(/\?$/, '');
        const [entity, column] = withoutOptional.split(':');

        if (!entity) continue;

        results.push({
            entity: entity.trim(),
            // default column to "id" if omitted, matching Laravel's convention
            column: (column ?? 'id').trim(),
            raw,
        });
    }

    return results;
}

/**
 * Insert `text` into `value` at the given selection range, replacing
 * the selected text (if any) and returning the new value + new caret position.
 */
export function insertAtCursor(
    value: string,
    selectionStart: number,
    selectionEnd: number,
    text: string,
): { value: string; caret: number } {
    const before = value.slice(0, selectionStart);
    const after = value.slice(selectionEnd);
    const next = before + text + after;
    return { value: next, caret: before.length + text.length };
}
