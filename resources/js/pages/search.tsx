// packages/truvoicer/tf-perspectives/resources/js/pages/search.tsx
import { useState, type FormEvent } from 'react';
import { router } from '@inertiajs/react';
import { Row, Col, Form, Button, Badge, Card } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faSearch, faFilter, faXmark } from '@fortawesome/free-solid-svg-icons';
import Layout from '../components/Layout';
import { PerspectiveCard } from '../components/PerspectiveCard';
import Pagination from '../components/Pagination';
import EmptyState from '../components/EmptyState';
import { useAuth } from '../hooks/useAuth';
import { categoryIcon } from '../lib/icons';
import type { Category, Paginator, Perspective } from '../types';

interface SearchProps {
    perspectives: Paginator<Perspective>;
    categories: Category[];
    popularTags: string[];
    filters: { q: string; category: string; tag: string; mood: string; sort: string };
}

export default function Search({
    perspectives,
    categories,
    popularTags,
    filters,
}: SearchProps) {
    const { user } = useAuth();
    const [q, setQ] = useState(filters.q ?? '');
    const [tag, setTag] = useState(filters.tag ?? '');
    const [mood, setMood] = useState(filters.mood ?? '');
    const [category, setCategory] = useState(filters.category ?? '');
    const [sort, setSort] = useState(filters.sort ?? 'recent');

    const apply = (overrides: Partial<typeof filters> = {}) => {
        router.get(
            '/search',
            {
                q: q || undefined,
                tag: tag || undefined,
                mood: mood || undefined,
                category: category || undefined,
                sort: sort !== 'recent' ? sort : undefined,
                ...overrides,
            },
            { preserveScroll: true, preserveState: true },
        );
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        apply();
    };

    const clearAll = () => {
        setQ('');
        setTag('');
        setMood('');
        setCategory('');
        setSort('recent');
        router.get('/search', {}, { preserveScroll: true, preserveState: true });
    };

    const hasFilters = Boolean(q || tag || mood || category);

    return (
        <Layout>
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
                                <Button
                                    size="lg"
                                    variant="outline-secondary"
                                    onClick={clearAll}
                                    title="Clear filters"
                                >
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
                                    apply({ category: e.target.value || undefined });
                                }}
                            >
                                <option value="">All categories</option>
                                {categories.map((c) => (
                                    <option key={c.id} value={c.slug}>{c.name}</option>
                                ))}
                            </Form.Select>
                        </Col>
                        <Col md={3}>
                            <Form.Label className="tf-label">Mood</Form.Label>
                            <Form.Select
                                value={mood}
                                onChange={(e) => {
                                    setMood(e.target.value);
                                    apply({ mood: e.target.value || undefined });
                                }}
                            >
                                <option value="">Any mood</option>
                                {['hopeful','weary','angry','curious','grateful','afraid','determined'].map((m) => (
                                    <option key={m} value={m}>{m}</option>
                                ))}
                            </Form.Select>
                        </Col>
                        <Col md={3}>
                            <Form.Label className="tf-label">Sort</Form.Label>
                            <Form.Select
                                value={sort}
                                onChange={(e) => {
                                    setSort(e.target.value);
                                    apply({ sort: e.target.value });
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
                                onBlur={() => apply({ tag: tag || undefined })}
                                placeholder="e.g. housing"
                            />
                        </Col>
                    </Row>

                    {popularTags.length > 0 && (
                        <div className="tf-tag-cloud mt-3">
                            <span className="text-muted small me-2">Popular:</span>
                            {popularTags.map((t) => (
                                <Badge
                                    key={t}
                                    bg={tag === t ? 'primary' : 'light'}
                                    text={tag === t ? 'light' : 'dark'}
                                    className="tf-tag"
                                    onClick={() => {
                                        const next = tag === t ? '' : t;
                                        setTag(next);
                                        apply({ tag: next || undefined });
                                    }}
                                >
                                    #{t}
                                </Badge>
                            ))}
                        </div>
                    )}
                </Form>
            </div>

            <div className="tf-panel">
                <div className="d-flex justify-content-between align-items-center mb-3">
                    <h2 className="tf-side-title mb-0">
                        {perspectives.total} result{perspectives.total === 1 ? '' : 's'}
                    </h2>
                </div>

                {perspectives.data.length === 0 ? (
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
                        {perspectives.data.map((p) => (
                            <PerspectiveCard
                                key={p.id}
                                perspective={p}
                                canInteract={Boolean(user)}
                                truncate
                            />
                        ))}
                    </div>
                )}

                <Pagination paginator={perspectives} />
            </div>
        </Layout>
    );
}
