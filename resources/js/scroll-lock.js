/*
 * x-scroll-lock — stops the page behind an open modal from scrolling.
 *
 *   <div x-show="open" x-scroll-lock="open">   // toggled with the state
 *   <template x-if="open"><div x-scroll-lock>  // locked while the node exists
 *
 * Locks are ref-counted so stacked modals can't unlock the page early, and the
 * scrollbar's width is added back as padding so the layout doesn't jump.
 */

let locks = 0;

const apply = () => {
    const scrollbar = window.innerWidth - document.documentElement.clientWidth;

    document.body.style.overflow = 'hidden';

    if (scrollbar > 0) {
        document.body.style.paddingRight = `${scrollbar}px`;
    }
};

const release = () => {
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
};

export function registerScrollLock(Alpine) {
    Alpine.directive('scroll-lock', (el, { expression }, { effect, evaluateLater, cleanup }) => {
        let locked = false;

        const setLocked = (value) => {
            if (value === locked) return;

            locked = value;

            if (locked && ++locks === 1) {
                apply();
            } else if (!locked && --locks === 0) {
                release();
            }
        };

        if (expression) {
            const shouldLock = evaluateLater(expression);
            effect(() => shouldLock((value) => setLocked(Boolean(value))));
        } else {
            setLocked(true);
        }

        // Covers x-if teardown and leaving the page with a modal still open.
        cleanup(() => setLocked(false));
    });
}
