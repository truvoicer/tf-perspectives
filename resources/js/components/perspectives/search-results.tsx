// packages/truvoicer/tf-perspectives/resources/js/pages/search.tsx
import { SetStateAction, useState, Dispatch } from 'react';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { router, usePage } from '@inertiajs/react';
import { Button } from 'react-bootstrap';
import { faSearch } from '@fortawesome/free-solid-svg-icons';
import { PerspectiveCard } from '../../components/perspectives/perspective-card';
import Pagination from '../../components/perspectives/pagination';
import EmptyState from '../../components/perspectives/empty-state';
import type { Category, Paginator, Perspective } from '../../types/perspectives';
import { route } from 'ziggy-js';

interface SearchResultsProps {
    perspectives: Paginator<Perspective>;
    categories: Category[];
    popularTags: string[];
    filters: { q: string; category: string; tag: string; mood: string; sort: string };
    tag: string;
    setTag: Dispatch<SetStateAction<string>>;
    mood: string;
    setMood: Dispatch<SetStateAction<string>>;
    category: string;
    setCategory: Dispatch<SetStateAction<string>>;
    sort: string;
    setSort: Dispatch<SetStateAction<string>>;
    q: string;
    setQ: Dispatch<SetStateAction<string>>;
    threadPagePermalink?: string;
    feedPagePermalink?: string;
    categoryThreadPagePermalink?: string;
    categoriesPagePermalink?: string;
    searchPagePermalink?: string;
    searchResultsPagePermalink?: string;
}

export default function SearchResults({
    perspectives,
    categories,
    popularTags,
    tag,
    setTag,
    mood,
    setMood,
    category,
    setCategory,
    sort,
    setSort,
    q,
    setQ,
    threadPagePermalink,
    feedPagePermalink,
    categoryThreadPagePermalink,
    categoriesPagePermalink,
    searchPagePermalink,
    searchResultsPagePermalink,
}: SearchResultsProps) {
    const { user } = useAuth();


    const clearAll = () => {
        setQ('');
        setTag('');
        setMood('');
        setCategory('');
        setSort('recent');
        router.get(window.location.pathname, {}, {
            preserveScroll: true,
            preserveState: true,
            replace: true, // Optional: prevents history spam
        });
    };


    return (
        <>
            <div className="tf-panel">
                {typeof perspectives === 'object' && perspectives !== null && perspectives.hasOwnProperty('total') && (
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <h2 className="tf-side-title mb-0">
                            {perspectives.total} result{perspectives.total === 1 ? '' : 's'}
                        </h2>
                    </div>
                )}

                {Array.isArray(perspectives?.data) && perspectives.data.length === 0 ? (
                    <EmptyState
                        icon={faSearch}
                        title="Nothing matched"
                        body="Try a different keyword or clear your filters."
                        action={
                            <Button variant="outline-secondary" onClick={clearAll}>
                                Clear filters
                            </Button>
                        }
                    />
                ) : (
                    <div className="tf-feed">
                        {Array.isArray(perspectives?.data) && perspectives.data.length > 0 && perspectives.data.map((p) => (
                            <PerspectiveCard
                                key={p.id}
                                perspective={p}
                                canInteract={Boolean(user)}
                                truncate
                                threadPagePermalink={threadPagePermalink}
                                feedPagePermalink={feedPagePermalink}
                                categoryThreadPagePermalink={categoryThreadPagePermalink}
                                categoriesPagePermalink={categoriesPagePermalink}
                                searchPagePermalink={searchPagePermalink}
                                searchResultsPagePermalink={searchResultsPagePermalink}
                            />
                        ))}
                    </div>
                )}

                <Pagination paginator={perspectives} />
            </div>
            </>
    );
}
