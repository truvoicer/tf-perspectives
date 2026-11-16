// packages/truvoicer/tf-perspectives/resources/js/pages/search.tsx
import { faSearch } from '@fortawesome/free-solid-svg-icons';
import { router, usePage } from '@inertiajs/react';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { Dispatch, SetStateAction, useMemo, useState } from 'react';
import type { Category, Paginator, Perspective } from '../../../types/perspectives';
import SearchResults from '../search-results';

interface SearchResultsProps {
}

export default function SearchResultsBlock({
}: SearchResultsProps) {
    const { user } = useAuth();

    const {
        perspectives,
        categories,
        popularTags,
        filters,
        searchResultsPagePermalink,
        threadPagePermalink,
        feedPagePermalink,
        categoryThreadPagePermalink,
        categoriesPagePermalink,
        searchPagePermalink
    } = usePage<{
        perspectives: Paginator<Perspective>;
        popularTags: string[];
        filters: { q: string; category: string; tag: string; mood: string; sort: string };
        searchResultsPagePermalink?: string;
        categories: Category[];
        threadPagePermalink?: string;
        feedPagePermalink?: string;
        categoryThreadPagePermalink?: string;
        categoriesPagePermalink?: string;
        searchPagePermalink?: string;
    }>().props;

    const [q, setQ] = useState(filters?.q ?? '');
    const [tag, setTag] = useState(filters?.tag ?? '');
    const [mood, setMood] = useState(filters?.mood ?? '');
    const [category, setCategory] = useState(filters?.category ?? '');
    const [sort, setSort] = useState(filters?.sort ?? 'recent');


    return (
        <SearchResults
            perspectives={perspectives}
            categories={categories}
            popularTags={popularTags}
            tag={tag}
            setTag={setTag}
            mood={mood}
            setMood={setMood}
            category={category}
            setCategory={setCategory}
            sort={sort}
            setSort={setSort}
            q={q}
            setQ={setQ}
            filters={filters}
            threadPagePermalink={threadPagePermalink}
            feedPagePermalink={feedPagePermalink}
            categoryThreadPagePermalink={categoryThreadPagePermalink}
            categoriesPagePermalink={categoriesPagePermalink}
            searchPagePermalink={searchPagePermalink}
            searchResultsPagePermalink={searchResultsPagePermalink}
        />
    );
}
