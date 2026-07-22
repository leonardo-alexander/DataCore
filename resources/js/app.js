import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { createIcons, icons } from 'lucide';
import { registerTourStore } from './tour';

Alpine.plugin(collapse);
document.addEventListener('alpine:init', () => registerTourStore(Alpine));
window.Alpine = Alpine;
Alpine.start();

const renderIcons = () => createIcons({ icons });
// Exposed so dynamically-rendered UI (e.g. the survey builder adding questions)
// can re-scan for new [data-lucide] placeholders after Alpine updates the DOM.
window.renderIcons = renderIcons;
document.addEventListener('DOMContentLoaded', renderIcons);
document.addEventListener('alpine:initialized', renderIcons);
