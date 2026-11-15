// packages/truvoicer/tf-perspectives/resources/js/components/Pagination.tsx
import { Link } from '@inertiajs/react';
import type { Paginator } from '../../types/perspectives';

interface PaginationProps<T> {
    paginator?: Paginator<T> | null;
}

export default function Pagination<T>({ paginator }: PaginationProps<T>) {
    if (!paginator) return null;

    const { last_page, links } = paginator;
    if (last_page <= 1) return null;

    return (
        <nav className="tf-pagination" aria-label="Pagination">
            {links.map((link, index) => {
                const label = link.label
                    .replace('&laquo;', '‹').replace('&raquo;', '›')
                    .replace('&hellip;', '…');

                if (!link.url) {
                    return (
                        <span
                            key={index}
                            className="tf-page-link is-disabled"
                            dangerouslySetInnerHTML={{ __html: label }}
                        />
                    );
                }
                return (
                    <Link
                        key={index}
                        href={link.url}
                        className={`tf-page-link ${link.active ? 'is-active' : ''}`}
                        preserveScroll
                        dangerouslySetInnerHTML={{ __html: label }}
                    />
                );
            })}
        </nav>
    );
}
