const STORAGE_KEY = 'dc_tour_state';

function readState() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch (e) {
        return null;
    }
}

function sameUrlPath(url) {
    try {
        return new URL(url, window.location.origin).pathname === window.location.pathname;
    } catch (e) {
        return false;
    }
}

export function registerTourStore(Alpine) {
    Alpine.store('tour', {
        active: false,
        section: null,
        step: 0,
        bound: null,

        get sections() {
            const el = document.getElementById('dc-guide-data');
            if (!el) return [];
            try {
                return JSON.parse(el.textContent);
            } catch (e) {
                return [];
            }
        },

        get currentSection() {
            return this.section === null ? null : (this.sections[this.section] ?? null);
        },

        get currentStep() {
            return this.currentSection?.items?.[this.step] ?? null;
        },

        get isLastStep() {
            const items = this.currentSection?.items ?? [];
            return this.step >= items.length - 1;
        },

        init() {
            const saved = readState();
            if (!saved || saved.section === null || saved.section === undefined) return;

            this.section = saved.section;
            this.step = saved.step ?? 0;
            this.active = true;
            this._locate();
        },

        start(sectionIdx) {
            this.section = sectionIdx;
            this.step = 0;
            this.active = true;
            this._goToStep();
        },

        goTo(stepIdx) {
            if (!this.active) return;
            this.step = stepIdx;
            this._goToStep();
        },

        next() {
            if (!this.active) return;
            if (this.isLastStep) {
                this.close();
                return;
            }
            this.step++;
            this._goToStep();
        },

        prev() {
            if (!this.active || this.step === 0) return;
            this.step--;
            this._goToStep();
        },

        close() {
            this.active = false;
            this.bound = null;
            document.body.style.overflow = '';
            localStorage.removeItem(STORAGE_KEY);
        },

        _persist() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify({ section: this.section, step: this.step }));
        },

        _goToStep() {
            this._persist();
            const item = this.currentStep;
            if (!item) {
                this.close();
                return;
            }
            if (item.url && !sameUrlPath(item.url)) {
                window.location.href = item.url;
                return;
            }
            this._locate();
        },

        _locate() {
            this.bound = null;
            document.body.style.overflow = 'hidden';

            const item = this.currentStep;
            if (!item || !item.target) return;

            let tries = 0;
            const tryFind = () => {
                const el = document.querySelector(`[data-tour="${item.target}"]`);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        const r = el.getBoundingClientRect();
                        this.bound = { top: r.top, left: r.left, width: r.width, height: r.height, bottom: r.bottom };
                    }, 380);
                    return;
                }
                if (tries < 10) {
                    tries++;
                    setTimeout(tryFind, 150);
                }
            };
            tryFind();
        },
    });
}
