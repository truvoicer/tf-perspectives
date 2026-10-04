// packages/truvoicer/tf-perspectives/resources/js/components/ReactionBar.tsx
import { router } from '@inertiajs/react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faShoePrints, faLightbulb, faHandshake, faCircleQuestion,
} from '@fortawesome/free-solid-svg-icons';
import type { ReactionSummary, ReactionType } from '../types';

const REACTIONS: Array<{
    type: ReactionType; icon: any; label: string; tooltip: string;
}> = [
    { type: 'empathy', icon: faShoePrints,     label: 'Empathy',  tooltip: 'I have stood in these shoes' },
    { type: 'insight', icon: faLightbulb,      label: 'Insight',  tooltip: 'This reframed something for me' },
    { type: 'relate',  icon: faHandshake,      label: 'Relate',   tooltip: 'This is close to my own experience' },
    { type: 'curious', icon: faCircleQuestion, label: 'Curious',  tooltip: 'I want to know more' },
];

interface ReactionBarProps {
    perspectiveId: number;
    summary: ReactionSummary;
    disabled?: boolean;
    compact?: boolean;
}

export function ReactionBar({ perspectiveId, summary, disabled, compact }: ReactionBarProps) {
    const react = (type: ReactionType) => {
        router.post(
            `/perspectives/${perspectiveId}/react`,
            { type },
            { preserveScroll: true, preserveState: true },
        );
    };

    return (
        <div className={`tf-reactions ${compact ? 'is-compact' : ''}`}>
            {REACTIONS.map(({ type, icon, label, tooltip }) => {
                const count = summary.counts[type] ?? 0;
                const mine = summary.mine === type;
                return (
                    <button
                        key={type}
                        type="button"
                        title={tooltip}
                        className={`tf-reaction ${mine ? 'is-on' : ''}`}
                        disabled={disabled}
                        onClick={() => react(type)}
                    >
                        <FontAwesomeIcon icon={icon} />
                        <span className="tf-reaction-count">{count}</span>
                        {!compact && <span className="tf-reaction-label">{label}</span>}
                    </button>
                );
            })}
        </div>
    );
}
