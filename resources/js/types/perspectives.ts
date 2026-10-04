// packages/truvoicer/tf-perspectives/resources/js/types.ts
import type { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface User {
    id: number;
    name: string;
    handle: string;
    avatar_url: string | null;
    bio?: string | null;
    perspectives_count?: number;
    followers_count?: number;
    following_count?: number;
    is_following?: boolean;
}

export interface Author extends User {}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string;      // e.g. "users", "brain"
    color: string;     // hex
    perspectives_count?: number;
}

export type ReactionType = 'empathy' | 'insight' | 'relate' | 'curious';

export interface ReactionSummary {
    counts: Record<ReactionType, number>;
    mine: ReactionType | null;
}

export interface Perspective {
    id: number;
    parent_id: number | null;
    root_id: number | null;
    depth: number;
    voice: string | null;
    body: string;
    mood: string | null;
    is_anonymous: boolean;
    author: Author | null;
    category: Category | null;
    tags: string[];
    reactions: ReactionSummary;
    children_count: number;
    bookmark_count: number;
    is_bookmarked: boolean;
    can_branch: boolean;
    created_at: string | null;
}

export interface PerspectiveNode extends Perspective {
    children: PerspectiveNode[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
    next_page_url: string | null;
    prev_page_url: string | null;
}

export interface NotificationItem {
    id: string;
    type: string;
    read_at: string | null;
    created_at: string;
    actor: Author | null;
    perspective_id: number;
    perspective_excerpt: string;
    message: string;
}

export interface SharedProps extends InertiaPageProps {
    auth: { user: User | null };
    flash: { success?: string | null; error?: string | null };
    unread_notifications?: number;
    categories_nav?: Category[];
    [key: string]: unknown;
}
