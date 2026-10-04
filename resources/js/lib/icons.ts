// packages/truvoicer/tf-perspectives/resources/js/lib/icons.ts
import {
    faBrain, faBriefcase, faHeart, faUsers, faGraduationCap,
    faLeaf, faHouse, faScaleBalanced, faGlobe, faShieldHalved,
    faCommentDots, faShoePrints, faLightbulb, faHandshake,
    faEye, faCircleQuestion,
} from '@fortawesome/free-solid-svg-icons';
import type { IconDefinition } from '@fortawesome/fontawesome-svg-core';

const ICONS: Record<string, IconDefinition> = {
    brain: faBrain, briefcase: faBriefcase, heart: faHeart,
    users: faUsers, 'graduation-cap': faGraduationCap, leaf: faLeaf,
    house: faHouse, 'scale-balanced': faScaleBalanced, globe: faGlobe,
    'shield-halved': faShieldHalved, 'comment-dots': faCommentDots,
    'shoe-prints': faShoePrints, lightbulb: faLightbulb,
    handshake: faHandshake, eye: faEye, question: faCircleQuestion,
};

export function categoryIcon(name: string | undefined): IconDefinition {
    return ICONS[name ?? ''] ?? faLightbulb;
}
