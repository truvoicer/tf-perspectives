// packages/truvoicer/tf-perspectives/resources/js/components/Pagination.tsx
import { Link } from '@inertiajs/react';
import type { Paginator } from '../types';

interface PaginationProps<T> {
    paginator: Paginator<T>;
}

export default function Pagination<T>({ paginator }: PaginationProps<T>) {
    if (paginator.last_page <= 1) return null;

    return (
        <nav className="tf-pagination" aria-label="Pagination">
            {paginator.links.map((link, index) => {
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
