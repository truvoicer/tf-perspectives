// packages/truvoicer/tf-perspectives/resources/js/components/perspectives/stats.tsx
import {
    faChartLine,
    faChevronLeft,
    faChevronRight,
    faLayerGroup,
    faShoePrints,
    faUsers,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { router, usePage } from '@inertiajs/react';
import type { Paginator } from '../../types/perspectives';

interface StatItem {
    key: string;
    value: number;
    affix: string;
}

interface StatsBlockProps {
    /** Paginator where `data` is an array of stat groups (array of arrays). */
    stats?: Paginator<StatItem[]>;
}

const ICONS: Record<string, typeof faChartLine> = {
    total_perspectives: faShoePrints,
    total_branches: faLayerGroup,
    total_voices: faUsers,
    today: faChartLine,
};

export default function StatsBlock({ stats: providedStats }: StatsBlockProps) {
    const page = usePage<{ stats: Paginator<StatItem[]> }>();
    const stats = providedStats ?? page.props.stats;

    if (!stats || !Array.isArray(stats.data) || stats.data.length === 0) {
        return null;
    }

    const canPrev = stats.current_page > 1;
    const canNext = stats.current_page < stats.last_page;

    const goToPage = (pageNumber: number) => {
        if (pageNumber < 1 || pageNumber > stats.last_page) return;

        router.reload( {
            data: { stats_page: pageNumber },
            only: ['stats'], // Only fetch the stats prop to avoid reloading everything
            preserveScroll: true, preserveState: true
        });
    };

    return (
        <div className="tf-stats-wrapper d-flex justify-content-between">
            <div className="tf-stats mt-0">
                {stats.data.map((group, groupIndex) => (
                    <div key={groupIndex} className="tf-stats-group">
                        {group.map((s) => {
                            const Icon = ICONS[s.key] ?? faChartLine;
                            return (
                                <div key={s.key} className="tf-stat">
                                    <FontAwesomeIcon icon={Icon} />
                                    <strong>{s.value.toLocaleString()}</strong>
                                    <span>{s.affix}</span>
                                </div>
                            );
                        })}
                    </div>
                ))}
            </div>

            {stats.last_page > 1 && (
                <div className="tf-stats-nav">
                    <button
                        type="button"
                        className="tf-stats-arrow"
                        onClick={() => goToPage(stats.current_page - 1)}
                        disabled={!canPrev}
                        aria-label="Previous stats page"
                    >
                        <FontAwesomeIcon icon={faChevronLeft} />
                    </button>
                    <button
                        type="button"
                        className="tf-stats-arrow"
                        onClick={() => goToPage(stats.current_page + 1)}
                        disabled={!canNext}
                        aria-label="Next stats page"
                    >
                        <FontAwesomeIcon icon={faChevronRight} />
                    </button>
                </div>
            )}
        </div>
    );
}
