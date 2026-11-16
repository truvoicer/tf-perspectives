// packages/truvoicer/tf-perspectives/resources/js/pages/search.tsx
import { faFilter, faSearch, faXmark } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { router, usePage } from '@inertiajs/react';
import { useAuth } from '@truvoicer/tru-fetcher-core-ui';
import { useEffect, useState, type SubmitEvent } from 'react';
import { Badge, Button, Col, Form, Row } from 'react-bootstrap';
import { route } from 'ziggy-js';
import type { Category, Paginator, Perspective } from '../../../types/perspectives';
import { UrlBuilder } from '../../../lib/url-builder';
import SearchResults from '../search-results';

interface SearchBlockProps {
    perspectives: Paginator<Perspective>;
    categories: Category[];
    popularTags: string[];
    filters: { q: string; category: string; tag: string; mood: string; sort: string };
}

export default function SearchBlock(props) {
    const { user } = useAuth();
    const {
        perspectives,
        categories,
        popularTags,
        filters,
        searchResultsPagePermalink,
        searchPagePermalink,
        threadPagePermalink,
        feedPagePermalink,
        categoryThreadPagePermalink,
        categoriesPagePermalink

    } = usePage<{
        perspectives: Paginator<Perspective>;
        popularTags: string[];
        filters: { q: string; category: string; tag: string; mood: string; sort: string };
        searchResultsPagePermalink?: string;
        searchPagePermalink?: string;
        categories: Category[];
        threadPagePermalink?: string;
        feedPagePermalink?: string;
        categoryThreadPagePermalink?: string;
        categoriesPagePermalink?: string;
    }>().props;
    const [q, setQ] = useState(filters?.q ?? '');
    const [tag, setTag] = useState(filters?.tag ?? '');
    const [mood, setMood] = useState(filters?.mood ?? '');
    const [category, setCategory] = useState(filters?.category ?? '');
    const [sort, setSort] = useState(filters?.sort ?? 'recent');

    const showSearchResults = props?.pageColumnBlock?.data?.show_search_results || false;

    const apply = (overrides: Partial<typeof filters> = {}) => {
        const searchParams = {
            q: q || undefined,
            tag: tag || undefined,
            mood: mood || undefined,
            category: category || undefined,
            sort: sort !== 'recent' ? sort : undefined,
            ...overrides,
        };
        if (showSearchResults) {
            router.reload({ data: searchParams, preserveScroll: true, preserveState: true });
        } else {
            router.get(
                UrlBuilder.buildSearchResultsPageUrl(searchResultsPagePermalink),
                searchParams,
                { preserveScroll: true, preserveState: true },
            );
        }
    };

    const submit = (e: SubmitEvent) => {
        e.preventDefault();
        apply();
    };

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

    const hasFilters = Boolean(q || tag || mood || category);
    useEffect(() => {
        router.reload({ only: ['categories'] });
    }, []);
    return (
        <>
            <div className="tf-panel mb-4">
                <h1 className="tf-page-title">
                    <FontAwesomeIcon icon={faSearch} className="me-2" />
                    Search perspectives
                </h1>

                <Form onSubmit={submit} className="tf-search-form">
                    <Row className="g-2">
                        <Col md={8}>
                            <Form.Control
                                size="lg"
                                type="search"
                                value={q}
                                onChange={(e) => setQ(e.target.value)}
                                placeholder="Search voices, bodies, tags…"
                            />
                        </Col>
                        <Col md={4} className="d-flex gap-2">
                            <Button size="lg" variant="primary" type="submit" className="flex-grow-1">
                                Search
                            </Button>
                            {hasFilters && (
                                <Button size="lg" variant="outline-secondary" onClick={clearAll} title="Clear filters">
                                    <FontAwesomeIcon icon={faXmark} />
                                </Button>
                            )}
                        </Col>
                    </Row>

                    <Row className="g-3 mt-3">
                        <Col md={3}>
                            <Form.Label className="tf-label">
                                <FontAwesomeIcon icon={faFilter} className="me-1" />
                                Category
                            </Form.Label>
                            <Form.Select
                                value={category}
                                onChange={(e) => {
                                    setCategory(e.target.value);
                                }}
                            >
                                <option value="">All categories</option>
                                {Array.isArray(categories) &&
                                    categories.map((c) => (
                                        <option key={c.id} value={c.slug}>
                                            {c.name}
                                        </option>
                                    ))}
                            </Form.Select>
                        </Col>
                        <Col md={3}>
                            <Form.Label className="tf-label">Mood</Form.Label>
                            <Form.Select
                                value={mood}
                                onChange={(e) => {
                                    setMood(e.target.value);
                                }}
                            >
                                <option value="">Any mood</option>
                                {['hopeful', 'weary', 'angry', 'curious', 'grateful', 'afraid', 'determined'].map((m) => (
                                    <option key={m} value={m}>
                                        {m}
                                    </option>
                                ))}
                            </Form.Select>
                        </Col>
                        <Col md={3}>
                            <Form.Label className="tf-label">Sort</Form.Label>
                            <Form.Select
                                value={sort}
                                onChange={(e) => {
                                    setSort(e.target.value);
                                }}
                            >
                                <option value="recent">Most recent</option>
                                <option value="popular">Most reacted</option>
                                <option value="branched">Most branched</option>
                            </Form.Select>
                        </Col>
                        <Col md={3}>
                            <Form.Label className="tf-label">Tag</Form.Label>
                            <Form.Control
                                value={tag}
                                onChange={(e) => setTag(e.target.value)}
                                placeholder="e.g. housing"
                            />
                        </Col>
                    </Row>

                    {Array.isArray(popularTags) && popularTags.length > 0 && (
                        <div className="tf-tag-cloud mt-3">
                            <span className="small me-2 text-muted">Popular:</span>
                            {popularTags.map((t) => (
                                <Badge
                                    key={t}
                                    bg={tag === t ? 'primary' : 'light'}
                                    text={tag === t ? 'light' : 'dark'}
                                    className="tf-tag"
                                    onClick={() => {
                                        const next = tag === t ? '' : t;
                                        setTag(next);
                                    }}
                                >
                                    #{t}
                                </Badge>
                            ))}
                        </div>
                    )}
                </Form>
            </div>
            {showSearchResults && (
                <SearchResults
                    perspectives={perspectives}
                    categories={categories}
                    popularTags={popularTags}
                    filters={{ q, tag, mood, category, sort }}
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
                    threadPagePermalink={threadPagePermalink}
                    feedPagePermalink={feedPagePermalink}
                    categoryThreadPagePermalink={categoryThreadPagePermalink}
                    categoriesPagePermalink={categoriesPagePermalink}
                    searchPagePermalink={searchPagePermalink}
                    searchResultsPagePermalink={searchResultsPagePermalink}
                />
            )}
        </>
    );
}
